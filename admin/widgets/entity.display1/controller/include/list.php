<?
use \Entities\EntitySectionTree;
use \Entities\Entity;
use \Entities\EntityItem;
use \Entities\EntityAdminDisplay;
use \Helpers\CArrayHelper;
use \Helpers\CArraySorter;

$userID = $this->app("user")->getID();

/*PerPage*/
$arPerPage = array(
    1   => "1",
    20  => "50",
    100 => "100",
    -1  => "Все"
);

$perPage = 20;

if(isset($_REQUEST["perPage"]) && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = $_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/

$arFormData = array(
    "relation"  => EntityItem::TYPE_ELEMENT,
    "type"      => "detail"
);

if(is_array($_REQUEST["entity_display"])){
    $arFormData = array_merge($arFormData, $_REQUEST["entity_display"]);
}

if(!$obEntity->use_sections){
    $arFormData["relation"] = EntityItem::TYPE_ELEMENT;
}

$obEntityDisplay = EntityAdminDisplay::find("user_id=? 
                                             AND entity_id=? 
                                             AND entity_section_id=0
                                             AND relation=?", array($userID, $obEntity->entity_id, $arFormData["relation"]));

$dataField = ($arFormData["type"] == "list") ? "list_data" : "detail_data";

$entityHasItsStructure = false;

if($obEntityDisplay && strlen($obEntityDisplay->{$dataField})){
    $entityHasItsStructure = true;
}

$arEntitySections = array();

if($obEntity->use_sections){
    $arEntitySections = EntitySectionTree::getTreeList(array(
        "pagination"    => $obPagination,
        "condition"     => "entity_id=?"
    ), array($obEntity->entity_id));
    
    $arEntitySections       = CArrayHelper::index($arEntitySections, "entity_item_id");
    $arSectionInheritance   = EntityAdminDisplay::getSectionInheritance(array_keys($arEntitySections), $userID, $arFormData["relation"], $arFormData["type"]);
}

$this->setData(array(
    "arSectionInheritance"  => $arSectionInheritance,
    "arFormData"            => $arFormData,
    "obEntityDisplay"       => $obEntityDisplay,
    "arEntitySections"      => $arEntitySections,
    "entityHasItsStructure" => $entityHasItsStructure,
    
    
    
    "entityURL"             => $entityURL,
    "obEntity"              => $obEntity,
    "editURL"               => $editURL,
    "listURL"               => $listURL,
    "obPagination"          => $obPagination,
));

$this->includeView("list");
?>