<?
use \Entities\EntitySection;
use \Entities\EntityItem;
use \Entities\EntityField;
use \Entities\EntityAdminDisplay;
use \Entities\EntityFieldTypes\FieldTypeBase;
use \Entities\EntitySectionFieldValue;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$userID = $this->app("user")->getID();

/*PerPage*/
$arPerPage = array(
    20  => "Показать по 20",
    50  => "Показать по 50",
    100 => "Показать по 100",
    -1  => "Показать все"
);

$perPage = key($arPerPage);

if($_REQUEST["perPage"] && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = (int)$_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/

$arEntityFields = EntityField::findAll("entity_id=?", array($obEntity->entity_id));
$arEntityFields = CArrayHelper::index($arEntityFields, "entity_field_id");


$obFieldTypeBase    = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);
$arBaseFieldsInfo   = $obFieldTypeBase->getInfo();

$arSqlParams["params"] = array(
    "select"        => "t1.*",
    "alias"         => "t1",
    "condition"     => "t1.entity_id=?",
    "pagination"    => $obPagination,
    "order"         => "t1.entity_item_id DESC"
);

$arSqlParams["statements"] = array($obEntity->entity_id);

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        switch($_REQUEST["group"]){
            case "activate":
                EntitySection::updateAllByPk($arGroupItems, array("active" => 1));
                break;
            case "deactivate":
                EntitySection::updateAllByPk($arGroupItems, array("active" => 0));
                break;
            case "delete":
                EntitySection::deleteAllByPk($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/

/*Apply Request Sort*/
if($_REQUEST["sort"]){
    $sortField  = $_REQUEST["sort"];
    
    $sortBy     = htmlspecialchars($_REQUEST["by"]);
    $sortBy     = $sortBy == "asc" ? "ASC" : "DESC" ;
            
    if(substr($sortField, 0, 2) == "f_"){
        $sortField = substr($sortField, 2);
        
        if(isset($arEntityFields[$sortField])){
            $obFieldType    = EntityField::getType($arEntityFields[$sortField]);
            $arSqlParams    = $obFieldType->getSqlParams($arSqlParams, array(
                "by" => $sortBy
            ), "list.sort");
        }
    }else if(isset($arBaseFieldsInfo[$sortField])){
        $obFieldType = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);
        $arSqlParams = $obFieldType->getSqlParams($arSqlParams, array(
            "fieldName" => $sortField,
            "by"        => $sortBy
        ), "list.sort");
    }
}
/*Apply Request Sort*/

/*Apply Request Filter*/
if(is_array($_REQUEST["f"])){
    foreach($_REQUEST["f"] AS $field => $value){
        if(substr($field, 0, 2) == "f_"){
            $field = substr($field, 2);
            
            if(isset($arEntityFields[$field])){
                $obFieldType    = EntityField::getType($arEntityFields[$field]);
                $arSqlParams    = $obFieldType->getSqlParams($arSqlParams, array(
                    "value" => $value
                ), "list.filter");
            }
        }else if(isset($arBaseFieldsInfo[$field])){
            $obFieldType = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);
            $arSqlParams = $obFieldType->getSqlParams($arSqlParams, array(
                "fieldName" => $field,
                "value"     => $value
            ), "list.filter");
        }
    }
}
/*Apply Request Filter*/

$arEntitySections   = EntitySection::findAll($arSqlParams["params"], $arSqlParams["statements"]);
$arEntitySectionIDs = CArrayHelper::getColumn($arEntitySections, "entity_item_id");

$sectionID = 0;

/*entity display*/
if($_REQUEST["entity_section_id"]){
    $sectionID = (int)$_REQUEST["entity_section_id"];
}

$arEntityDisplayList    = EntityAdminDisplay::getDisplayMap($obEntity->entity_id, $sectionID, $userID, EntityItem::TYPE_SECTION, "list");
$arEntityDisplayFilter  = EntityAdminDisplay::getDisplayMap($obEntity->entity_id, $sectionID, $userID, EntityItem::TYPE_SECTION, "filter");
/*entity display*/

/*Field Values*/
$arEntityFieldValues = array();

if(count($arEntitySectionIDs)){
    $arTmpEntityFieldValues = EntitySectionFieldValue::findAll(array(
        "condition" => "entity_item_id IN(" . implode(", ", $arEntitySectionIDs) . ")",
        "order"     => "entity_item_field_value_id ASC"
    ));
    
    foreach($arTmpEntityFieldValues AS $obEntityFieldValue){
        $arEntityFieldValues[$obEntityFieldValue->entity_field_id][$obEntityFieldValue->entity_item_id][$obEntityFieldValue->entity_item_field_value_id] = array(
            "value_text"    => $obEntityFieldValue->value_text,
            "value_string"  => $obEntityFieldValue->value_string,
            "value_num"     => $obEntityFieldValue->value_num,
        );
    }
}
/*Field Values*/

/*Base Fields*/
$arBaseFields = array();

foreach($arBaseFieldsInfo AS $fieldName => $arField){
    $arBaseFields[$fieldName] = new FieldTypeBase($obEntity, EntityItem::TYPE_SECTION);
    $arBaseFields[$fieldName]->prepareListData(array(
        "arItems"   => $arEntitySections,
        "field"     => $arField,
        "fieldName" => $fieldName,
    ));
}
/*Base Fields*/

/*Extra Fields*/
foreach($arEntityFields AS &$obEntityField){
    $obEntityField->params      = CJSON::decode($obEntityField->params, true);
    $obEntityField->obFieldType = EntityField::getType($obEntityField);
    $obEntityField->obFieldType->prepareListData(array(
        "arValues"  => $arEntityFieldValues[$obEntityField->entity_field_id],
        "arItems"   => $arEntitySections
    ));
}

unset($obEntityField);
/*Extra Fields*/

/*Entity Elements Filter*/
$filterID = "entity_sections";

$obAdminTableListFilter = new \Admin\CAdminTableListFilter(array(
    "filterID"  => $filterID,
    "onApply"   => "onApplyFilter"
));
/*Entity Elements Filter*/

/*Entity List*/
$tableID = "entity_sections";

$obAdminTableList = new \Admin\CAdminTableList(array(
    "tableID"           => $tableID,
    "tableAttributes"   => array("class" => "table table-striped table-bordered table-hover"),
    "url"               => $listSectionURL,
    "onApplyAfter"      => "onApplyTableList"
));

$obAdminTableList->setPerPage($arPerPage);
$obAdminTableList->addPagination($obPagination);

$arHeaders = array();

foreach($arEntityDisplayList AS $arField){
    if($arField["isBase"]){
        $fieldName = $arField["field"];
        
        if($arBaseFieldsInfo[$fieldName]){
            $arHeaders[] = array(
                "title"     => $arBaseFieldsInfo[$fieldName]["title"],
                "sortable"  => true,
                "field"     => $fieldName
            );
        }
    }else{
        $fieldID = $arField["field"];
        $obField = $arEntityFields[$fieldID];
        
        if($obField){
            $arHeaders[] = array(
                "title"     => $obField->title,
                "sortable"  => true,
                "field"     => "f_" . $obField->entity_field_id
            );
        }
    }
}
    
$arHeaders[] = array(
    "title"         => "Действие",
    "attributes"    => array(
        "style" => "width: 125px;"
    )
);
    
$obAdminTableList->addHeaders($arHeaders);

$obAdminTableList->addGroupOperations(array(
    array(
        "title" => "Активировать",
        "value" => "activate"
    ),
    array(
        "title" => "Деактивировать",
        "value" => "deactivate"
    ),
    array(
        "title" => "Удалить",
        "value" => "delete"
    )
));
/*Entity Elements List*/

$this->setData(array(
    "obAdminTableListFilter"=> $obAdminTableListFilter,
    "obAdminTableList"      => $obAdminTableList,
    "arBaseFields"          => $arBaseFields,
    "arEntityFields"        => $arEntityFields,
    "arEntityDisplayList"   => $arEntityDisplayList,
    "arEntityDisplayFilter" => $arEntityDisplayFilter,
    "obEntity"              => $obEntity,
    "arEntitySections"      => $arEntitySections,
    "addElementURL"         => $addElementURL,
    "addSectionURL"         => $addSectionURL,
    "editElementURL"        => $editElementURL,
    "editSectionURL"        => $editSectionURL,
    "listElementURL"        => $listElementURL,
    "listSectionURL"        => $listSectionURL,
    "displaySettingsURL"    => BASE_URL . "settings/entities/" . $obEntity->entity_id . "/display/?entity_display[relation]=" . EntityItem::TYPE_SECTION . "&entity_display[type]=list"
));

$this->includeView("listSection");
?>