<?
use \Entities\Entity;
use \Entities\EntityAccess;
use \Models\UserGroup;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$obEntity       = Entity::findByPk($entityID);
$arEntityAccess = EntityAccess::findAll("entity_id=?", array($obEntity->entity_id));
$arEntityAccess = CArrayHelper::index($arEntityAccess, "user_group_id");

if(!$obEntity){
    CHttpResponse::redirect("/404/");
}

$arUserGroups       = UserGroup::findAll();
$arAccessRules      = EntityAccess::getAccessRules();
$arAccessRuleList   = CArrayHelper::getKeyValue($arAccessRules, false, "title");

$arErrors = array();

if($_REQUEST["user_group"]){
    $arData = $_REQUEST["user_group"];
    
    //получаем все группы пользователей
    $arUserGroups = \Models\UserGroup::findAll();
    
    foreach($arUserGroups AS $obUserGroup){ //убеждаемся, что для каждой группы выбран уровень доступа
        if(!strlen($arData[$obUserGroup->user_group_id])){
            $arErrors[$obUserGroup->user_group_id][] = "Выберите уровень доступа [" . $obUserGroup->title . "]";
        }
    }
    
    if(!count($arErrors)){
        $userGroupIDs   = CArrayHelper::getColumn($arUserGroups, "user_group_id");
        $arData         = EntityAccess::getSafeFields($arData, $userGroupIDs);
        
        //получаем текущие уровни доступа к группам
        $arEntityAccess = EntityAccess::findAll("entity_id=?", array($entityID));
        $arEntityAccess = CArrayHelper::index($arEntityAccess, "user_group_id");
        
        $arEntityAccessIDs = array();
        
        foreach($arData AS $userGroupID => $access){
            if($arEntityAccess[$userGroupID]){ //если у группы уже есть доступ в бд, то обновим запись
                $obEntityAccess = $arEntityAccess[$userGroupID];
                
                EntityAccess::updateByPk($obEntityAccess->entity_access_id, array(
                    "access" => $access
                ));
                
                $arEntityAccessIDs[] = $obEntityAccess->entity_access_id;
            }else{ //иначе добавим новую
                $entityAccessID = EntityAccess::add(array(
                    "user_group_id" => $userGroupID,
                    "entity_id"     => $entityID,
                    "access"        => $access
                ));
                
                if($entityAccessID){
                    $arEntityAccessIDs[] = $entityAccessID;
                }
            }
        }
        
        $arSaveResult["success"]    = true;
        $arSaveResult["id"]         = $arEntityAccessIDs;
    }else{
        $arSaveResult["hasErrors"]  = true;
        $arSaveResult["success"]    = false;
        $arSaveResult["errors"]     = $arErrors;
    }
    
    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = str_replace("{ENTITY_ID}", $arSaveResult["id"], $listURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }

        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $listURL = str_replace("{ENTITY_ID}", $arSaveResult["id"], $listURL);
            
            CHttpResponse::redirect($listURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
    }
}

\Page\Breadcrumbs::add(array(
    $entityURL  => "Редактирование сущности",
    $listURL    => "Настройка доступа"
));

$this->setData(array(
    "arEntityAccess"        => $arEntityAccess,
    "arAccessRuleList"      => $arAccessRuleList,
    "arUserGroups"          => $arUserGroups,
    "obEntity"              => $obEntity,
    "entityURL"             => $entityURL,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
));

$this->includeView("list");
?>