<?
use \Entities\EntityField;
use \Entities\EntityItem;
use \Entities\EntityFieldVariant;
use \Entities\Entity;
use \Helpers\CArrayHelper;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$fieldID = (int)$obRoute->getVarValue("FIELD_ID");

$obEntityField = EntityField::find("entity_field_id=? AND entity_id=?", array($fieldID, $obEntity->entity_id));

if(!$obEntityField){
    CHttpResponse::redirect("/404/");
}

$obEntityField->params = CJSON::decode($obEntityField->params, true);

$arFormData = (array)$obEntityField;

$arErrors = array();

if($_REQUEST["entity_field"]){
    $arFormData             = $_REQUEST["entity_field"];
    $arFormData["entity_id"]= $obEntity->entity_id;
    
    if(!$obEntity->use_sections){
        $arFormData["relation"] = EntityItem::TYPE_ELEMENT;
    }
    
    $arSaveResult = EntityField::updateByPk($obEntityField->entity_field_id, $arFormData);
    
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
    $editURL => "Поле: " . $obEntityField->title
));

$editURL = str_replace("{FIELD_ID}", $fieldID, $editURL);

$this->setData(array(
    "obEntity"                  => $obEntity,
    "obFieldType"               => $obFieldType,
    "arFieldTypeOptionList"     => $arFieldTypeOptionList,
    "arErrors"                  => $arErrors,
    "listURL"                   => $listURL,
    "editURL"                   => $editURL,
    "arFormData"                => $arFormData
));

$this->includeView("edit");
?>