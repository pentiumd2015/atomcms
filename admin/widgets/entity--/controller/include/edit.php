<?
use \Entities\Entity;
use \Entities\EntityGroup;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$entityID = (int)$obRoute->getVarValue("ID");

$obEntity = Entity::findByPk($entityID);

if(!$obEntity){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["entity"]) ? $_REQUEST["entity"] : (array)$obEntity;

$arErrors = array();

if($_REQUEST["entity"]){
    $arSaveResult = Entity::updateByPk($obEntity->entity_id, $_REQUEST["entity"]);
    
    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = str_replace("{ID}", $arSaveResult["id"], $editURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $editURL = str_replace("{ID}", $arSaveResult["id"], $editURL);
            
            CHttpResponse::redirect($editURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
   }
}

$arEntityGroups = EntityGroup::findAll(array(
    "order" => "title"
));

$arEntityGroupOptionList = CArrayHelper::getKeyValue($arEntityGroups, "entity_group_id", "title");

\Page\Breadcrumbs::add(array(
    $listURL    => "Список сущностей",
    $editURL    => "Редактирование сущности"
));

$this->setData(array(
    "arEntityGroupOptionList"   => $arEntityGroupOptionList,
    "arErrors"                  => $arErrors,
    "listURL"                   => $listURL,
    "editURL"                   => str_replace("{ID}", $obEntity->entity_id, $editURL),
    "arFormData"                => $arFormData
));

$this->includeView("edit");
?>