<?
use \Entities\EntityField;
use \Entities\EntityItem;
use \Entities\Entity;
use \Helpers\CArrayHelper;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$arFormData = isset($_REQUEST["entity_field"]) ? $_REQUEST["entity_field"] : array(
    "type"      => EntityField::FIELD_TYPE_STRING,
    "priority"  => 10,
    "relation"  => EntityItem::TYPE_ELEMENT
);

$arErrors = array();

if($_REQUEST["entity_field"]){
    $arFormData                 = $_REQUEST["entity_field"];
    $arFormData["entity_id"]    = $obEntity->entity_id;
    
    if(!$obEntity->use_sections){
        $arFormData["relation"] = EntityItem::TYPE_ELEMENT;
    }
    
    $arSaveResult = EntityField::add($arFormData);

    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = str_replace("{FIELD_ID}", $arSaveResult["id"], $editURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $editURL = str_replace("{FIELD_ID}", $arSaveResult["id"], $editURL);
            
            CHttpResponse::redirect($editURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
    }
}

$obEntityField = new \stdClass;
$obEntityField->type = $arFormData["type"];

$obFieldType = EntityField::getType($obEntityField); //get object field type
$obFieldType->setData(array(
    "arRequestField" => $arFormData
));

$arFieldTypeOptionList  = array("" => "Выберите тип");

foreach(EntityField::getTypes() AS $fieldType => $fieldTypeClass){
    $obTmpEntityField = new \stdClass;
    $obTmpEntityField->type = $fieldType;
    
    $obType                             = new $fieldTypeClass($obTmpEntityField);
    $arFieldTypeParams                  = $obType->getInfo();
    $arFieldTypeOptionList[$fieldType]  = $arFieldTypeParams["title"];
}

\Page\Breadcrumbs::add(array(
    $addURL => "Добавление нового поля"
));

$this->setData(array(
    "obEntity"              => $obEntity,
    "obFieldType"           => $obFieldType,
    "arFieldTypeOptionList" => $arFieldTypeOptionList,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "editURL"               => $editURL,
    "arFormData"            => $arFormData
));

$this->includeView("add");
?>