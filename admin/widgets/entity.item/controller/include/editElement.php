<?
use \Entities\Entity;
use \Entities\EntityItem;
use \Entities\EntityElement;
use \Entities\EntityField;
use \Entities\EntityElementFieldValue;
use \Entities\EntityFieldVariant;
use \Entities\EntitySectionElement;
use \Entities\EntityAdminDisplay;
use \Entities\EntityFieldTypes\FieldTypeBase;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$userID             = $this->app("user")->getID();

$entityElementID    = (int)$obRoute->getVarValue("ID");
$obEntityElement    = EntityElement::findByPk($entityElementID);

if(!$obEntityElement){
    CHttpResponse::redirect("/404/");
}

$arFormData = (array)$obEntityElement;
$arErrors   = array();

if($_REQUEST["entity_item"]){
    $arFormData                 = $_REQUEST["entity_item"];
    $arFormData["creator_id"]   = $userID;
    $arSaveResult               = EntityElement::updateByPk($obEntityElement->entity_item_id, $arFormData);

    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["itemID"];
            $arResponse["redirectURL"]  = str_replace("{ID}", $arSaveResult["itemID"], $editElementURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $redirectURL = str_replace("{ID}", $arSaveResult["itemID"], $editElementURL);
            
            CHttpResponse::redirect($editElementURL);
        }else{
            if($arSaveResult["errors"]){
                $arErrors = $arSaveResult["errors"];
            }
            
            if($arSaveResult["fields"]["errors"]){
                $arErrors["fields"] = $arSaveResult["fields"]["errors"];
            }
        }
    }
}

/*entity display*/
$obSectionItem = EntitySectionElement::find("entity_element_id=?", array($obEntityElement->entity_item_id));

$sectionID = 0;

if($obSectionItem){
    $sectionID = $obSectionItem->entity_section_id;
}

$arEntityDisplay = EntityAdminDisplay::getDisplayMap($obEntity->entity_id, $sectionID, $userID, EntityItem::TYPE_ELEMENT, "detail");
/*entity display*/

/*Field Values*/
$arTmpEntityFieldValues = EntityElementFieldValue::findAll(array(
    "condition" => "entity_item_id=?",
    "order"     => "entity_item_field_value_id ASC"
), array($entityElementID));

if($_REQUEST["entity_item"]){
    $arEntityFieldValues = $_REQUEST["entity_item"];
}else{
    foreach($arTmpEntityFieldValues AS $obEntityFieldValue){
        $arEntityFieldValues[$obEntityFieldValue->entity_field_id][$obEntityFieldValue->entity_item_field_value_id] = array(
            "value_text"    => $obEntityFieldValue->value_text,
            "value_string"  => $obEntityFieldValue->value_string,
            "value_num"     => $obEntityFieldValue->value_num,
        );
    }
}
/*Field Values*/

/*Base Fields*/
$arBaseFields = array();

$obFieldTypeBase = new FieldTypeBase($obEntity, EntityItem::TYPE_ELEMENT);

foreach($obFieldTypeBase->getInfo() AS $fieldName => $arField){
    $arBaseFields[$fieldName] = new FieldTypeBase($obEntity, EntityItem::TYPE_ELEMENT);
    $arBaseFields[$fieldName]->prepareDetailData(array(
        "arErrors"  => $arErrors[$fieldName],
        "field"     => $arField,
        "fieldName" => $fieldName,
        "arItem"    => $arFormData
    ));
}
/*Base Fields*/

/*Fields*/
$arEntityFields = EntityField::findAll("entity_id=? AND relation=?", array($obEntity->entity_id, EntityItem::TYPE_ELEMENT));
$arEntityFields = CArrayHelper::index($arEntityFields, "entity_field_id");

foreach($arEntityFields AS &$obEntityField){
    $obEntityField->params      = CJSON::decode($obEntityField->params, true);    
    $obEntityField->obFieldType = EntityField::getType($obEntityField);
    $obEntityField->obFieldType->prepareDetailData(array(
        "arValues" => $arEntityFieldValues[$obEntityField->entity_field_id],
        "arErrors" => $arErrors["fields"][$obEntityField->entity_field_id]
    ));
}

unset($obEntityField);
/*Fields*/

$eItemURL = str_replace("{ID}",  $obEntityElement->entity_item_id, $editElementURL);

\Page\Breadcrumbs::add(array(
    $listElementURL => $obEntity->params["signatures"][Entity::SIGNATURE_ELEMENTS]["title"],
    $editElementURL => $obEntity->params["signatures"][Entity::SIGNATURE_ELEMENT]["title"]
));

$this->setData(array(
    "editElementURL"        => $editElementURL,
    "obEntity"              => $obEntity,
    "obEntityElement"       => $obEntityElement,
    "arBaseFields"          => $arBaseFields,
    "arEntityFields"        => $arEntityFields,
    "arEntityDisplay"       => $arEntityDisplay,
    "arErrors"              => $arErrors,
    "arFormData"            => $arFormData,
    "displaySettingsURL"    => "/admin/settings/entities/" . $obEntity->entity_id . "/display/?entity_display[relation]=" . EntityItem::TYPE_ELEMENT . "&entity_display[type]=detail"
));

$this->includeView("editElement");
?>