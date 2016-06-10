<?
use \Entity\ExtraField;
use \Entity\Entity;

$entityClass = $_REQUEST["entity"];

$obEntity = false;

if(class_exists($entityClass)){
    $obTmpEntity = new $entityClass;
    
    if($obTmpEntity instanceof Entity){
        $obEntity = $obTmpEntity;
    }
}

if(!$obEntity){
    CHttpResponse::redirect("/404/");
}

$fieldPk = ExtraField::getPk();

/*
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
*/

$arExtraFields = $obEntity->getExtraFields();

foreach($arExtraFields AS &$arExtraField){
    $fieldTypeClass = $arExtraField["type"];
    
    if(class_exists($fieldTypeClass)){
        $arExtraField["type"] = new $fieldTypeClass(ExtraField::getFieldNameById($arExtraField[$fieldPk]), $arExtraField, $obEntity);
    }else{
        $arExtraField["type"] = false;
    }
}

unset($arExtraField);

CBreadcrumbs::add(array(
    $editURL => "Настройка сущности"
));

$this->setData(array(
    "obEntity"      => $obEntity,
    "arExtraFields" => $arExtraFields,
    "editURL"       => $editURL
));

$this->includeView();
?>

<?php 

?>

<?php 

?>