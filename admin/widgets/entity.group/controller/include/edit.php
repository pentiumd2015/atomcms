<?
use \Entities\EntityGroup;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$entityGroupID = (int)$obRoute->getVarValue("ID");

$obEntityGroup = EntityGroup::findByPk($entityGroupID);

if(!$obEntityGroup){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["entity_group"]) ? $_REQUEST["entity_group"] : (array)$obEntityGroup;

$arErrors = array();

if($_REQUEST["entity_group"]){
    $arSaveResult = EntityGroup::updateByPk($obEntityGroup->entity_group_id, $_REQUEST["entity_group"]);
    
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

\Page\Breadcrumbs::add(array(
    $listURL    => "Список групп",
    $addURL     => "Редактирование группы"
));

$this->setData(array(
    "arErrors"          => $arErrors,
    "listURL"           => $listURL,
    "editURL"           => str_replace("{ID}", $obEntityGroup->entity_group_id, $editURL),
    "arFormData"        => $arFormData
));

$this->includeView("edit");
?>