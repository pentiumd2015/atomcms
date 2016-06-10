<?
use \Entities\Entity;
use \Entities\EntityItem;
use \Entities\EntitySection;
use \Entities\EntityField;
use \Entities\EntityAdminDisplay;
use \Entities\EntityFieldTypes\FieldTypeBase;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$userID     = $this->app("user")->getID();

$arFormData = array();
$arErrors   = array();

if($_REQUEST["entity_item"]){
    $arFormData                 = $_REQUEST["entity_item"];
    $arFormData["entity_id"]    = $obEntity->entity_id;
    $arFormData["creator_id"]   = $userID;
    $arSaveResult               = EntitySection::add($arFormData);

    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["itemID"];
            $arResponse["redirectURL"]  = str_replace("{ID}", $arSaveResult["itemID"], $editItemURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
            
            if($arSaveResult["fields"]){
                $arResponse["fields"] = $arSaveResult["fields"];
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $redirectURL = str_replace("{ID}", $arSaveResult["itemID"], $editItemURL);
            
            CHttpResponse::redirect($redirectURL);
        }else{
            if($arSaveResult["errors"]){
                $arErrors = $arSaveResult["errors"];
            }
        }
    }
}

/*entity display*/
$sectionID = 0;
$arEntityDisplay = EntityAdminDisplay::getDisplayMap($obEntity->entity_id, $sectionID, $userID, EntityItem::TYPE_SECTION, "detail");
/*entity display*/

/*Field Values*/
$arEntityFieldValues = array();

if($_REQUEST["entity_item"]){
    $arEntityFieldValues = $_REQUEST["entity_item"];
}
/*Field Values*/

/*Base Fields*/
$arBaseFields = array();

$obFieldTypeBase = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);

foreach($obFieldTypeBase->getInfo() AS $fieldName => $arField){
    $arBaseFields[$fieldName] = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);
    $arBaseFields[$fieldName]->prepareDetailData(array(
        "arErrors"  => $arErrors[$fieldName],
        "field"     => $arField,
        "fieldName" => $fieldName,
        "arItem"    => $arFormData
    ));
}
/*Base Fields*/

/*Fields*/
$arEntityFields = EntityField::findAll("entity_id=? AND relation=?", array($obEntity->entity_id, EntityItem::TYPE_SECTION));
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

\Page\Breadcrumbs::add(array(
    $listSectionURL => $obEntity->params["signatures"][Entity::SIGNATURE_SECTIONS]["title"],
    $addSectionURL  => $obEntity->params["signatures"][Entity::SIGNATURE_ADD_SECTION]["title"]
));

$this->setData(array(
    "listSectionURL"        => $listSectionURL,
    "editSectionURL"        => $editSectionURL,
    "addSectionURL"         => $addSectionURL,
    "obEntity"              => $obEntity,
    "arBaseFields"          => $arBaseFields,
    "arEntityFields"        => $arEntityFields,
    "arEntityDisplay"       => $arEntityDisplay,
    "arErrors"              => $arErrors,
    "arFormData"            => $arFormData,
    "displaySettingsURL"    => "/admin/settings/entities/" . $obEntity->entity_id . "/display/?entity_display[relation]=" . EntityItem::TYPE_SECTION . "&entity_display[type]=detail"
));

$this->includeView("addSection");
?>