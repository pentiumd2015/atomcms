<?
use \Entities\EntityField;
use \Entities\Entity;
use \Helpers\CArrayHelper;

/*PerPage*/
$arPerPage = array(
    1   => "1",
    50  => "50",
    100 => "100",
    -1  => "Все"
);

$perPage = 50;

if(isset($_REQUEST["perPage"]) && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = $_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/

$filterSQL = "";

$arStatements = array($entityID);

$arFormData = array(
    "relation"  => EntityField::FIELD_RELATION_ITEM
);

if(is_array($_REQUEST["filter_field"])){
    $arFormData = array_merge($arFormData, $_REQUEST["filter_field"]);
}

if(!$obEntity->use_sections){
    $arFormData["relation"] = EntityField::FIELD_RELATION_ITEM;
}

if($arFormData["relation"]){
    $filterSQL.= " AND relation=?";
    
    $arStatements[] = (int)$arFormData["relation"];
}

$arEntityFields = EntityField::findAll(array(
    "pagination"    => $obPagination,
    "order"         => "priority",
    "condition"     => "entity_id=?" . $filterSQL
), $arStatements);
/**/

$arFieldTypes = array();

foreach(EntityField::getTypes() AS $fieldType => $fieldTypeClass){
    $obType                     = new $fieldTypeClass($obEntity);
    $arFieldTypeParams          = $obType->getInfo();
    $arFieldTypes[$fieldType]   = $arFieldTypeParams;
}

$this->setData(array(
    "arFormData"        => $arFormData,
    "entityURL"         => $entityURL,
    "obEntity"          => $obEntity,
    "arFieldTypes"      => $arFieldTypes,
    "arEntityFields"    => $arEntityFields,
    "addURL"            => $addURL,
    "editURL"           => $editURL,
    "listURL"           => $listURL,
    "obPagination"      => $obPagination,
));

$this->includeView("list");
?>