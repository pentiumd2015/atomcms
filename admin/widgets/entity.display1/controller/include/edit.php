<?
use \Entities\EntityAdminDisplay;
use \Entities\EntityFieldTypes\FieldTypeBase;
use \Entities\EntitySection;
use \Entities\EntityItem;
use \Entities\EntityField;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CHtml;

$userID = $this->app("user")->getID();

$relation           = $_GET["relation"];
$type               = $_GET["type"];
$entitySectionID    = (int)$_GET["entity_section_id"];

if(!$relation || !isset(EntityItem::$arTypes[$relation]) || !in_array($type, array("list", "detail"))){
    CHttpResponse::redirect($listURL);
}

$arEntityDisplay = EntityAdminDisplay::getDisplayMap($obEntity->entity_id, $entitySectionID, $userID, $relation, $type);

/*Fields*/
$arEntityFields = EntityField::findAll("entity_id=? AND relation=?", array($entityID, $relation));
$arEntityFields = CArrayHelper::index($arEntityFields, "entity_field_id");
/*Fields*/

/*Получим весь список основных полей*/
$arBaseFields = array();

$obFieldTypeBase = new FieldTypeBase($obEntity, $relation);

foreach($obFieldTypeBase->getInfo() AS $fieldName => $arFieldInfo){
    $arBaseFields[$fieldName] = array(
        "field"  => $fieldName,
        "title"  => $arFieldInfo["title"]
    );
}

/*Получим весь список основных полей*/

if($type == "list"){
    \Page\Breadcrumbs::add(array(
        $editURL => EntityItem::$arTypes[$relation]["title"] . ". Список",
    ));
    
    \Page\Page::addJS($this->path . "js/list.js");
    \Page\Page::addCSS($this->path . "css/list.css");
}else{
    \Page\Breadcrumbs::add(array(
        $editURL => EntityItem::$arTypes[$relation]["title"] . ". Подробный просмотр",
    ));

    \Page\Page::addJS($this->path . "js/detail.js");
    \Page\Page::addCSS($this->path . "css/detail.css");
}

$editURL = strtr($editURL, array(
    "{RELATION}"    => $relation,
    "{TYPE}"        => $type
));

$this->setData(array(
    "relation"          => $relation,
    "type"              => $type,
    "inheritType"       => $inheritType,
    "obEntity"          => $obEntity,
    "entitySectionID"   => $entitySectionID,
    "arBaseFields"      => $arBaseFields,
    "arEntityFields"    => $arEntityFields,
    "arEntityDisplay"   => $arEntityDisplay,
    "listURL"           => $listURL,
    "editURL"           => $editURL
));

$this->includeView(($type == "list" ? "include/list" : "include/detail"));
?>