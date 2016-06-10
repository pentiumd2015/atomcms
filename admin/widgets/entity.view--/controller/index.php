<?
use \Entities\Entity;
use \Entities\EntityAdminView;
use \Entities\EntitySignature;
use \Entities\EntitySection;
use \Entities\EntityField;
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;

$type               = (int)$_GET["type"];
$entityID           = (int)$_GET["entity_id"];
$entitySectionID    = (int)$_GET["entity_section_id"];


$inheritType = 1;

$arEntityView = array();

if($entityID && in_array($type, array(EntityAdminView::TYPE_LIST, EntityAdminView::TYPE_DETAIL))){
    $obEntity = Entity::findByPk($entityID);
    
    if(!$obEntity){
        CHttpResponse::redirect("/404/");
    }
    
    if($entitySectionID){
        //...
        
        //TO DO найти по наследованию по разделам, 
    }else{
        $inheritType = 1; //entity
        
        $obEntityView = EntityAdminView::find("user_id=0 AND entity_id=? AND entity_section_id=0 AND type=?", array($obEntity->entity_id, $type));
        
        if($obEntityView){
            $arEntityView = json_decode($obEntityView->data, true);
            
            if(!is_array($arEntityView)){
                $arEntityView = array();
            }
        }
    }
}


/*Fields*/
$arEntityFields = EntityField::findAll("entity_id=?", array($entityID));
$arEntityFields = CArrayHelper::index($arEntityFields, "entity_field_id");
/*Fields*/

$arEntitySignatures = EntitySignature::findAll("entity_id=?", array($entityID));
$arEntitySignatures = CArrayHelper::getKeyValue($arEntitySignatures, "type", "user_title");

\Page\Breadcrumbs::add(array(
    BASE_URL . "entity_items/" => $arEntitySignatures[EntitySignature::SIGNATURE_ITEMS],
    $listURL                     => "Настройка вида",
));

$templatePath = $this->app("template")->templatePath;

\Page\Page::addJS($templatePath . "/js/plugins/forms/inputnum.jquery.js");

\Page\Page::addCSS($templatePath . "/css/bootstrap.checkbox.css");

\Page\Page::addJS($templatePath . "/js/plugins/forms/select.jquery.js");
\Page\Page::addCSS($templatePath . "/css/select.jquery.css");


\Page\Page::addJS($this->path . "js/table.sortable.jquery.js");
\Page\Page::addJS($this->path . "js/itemViewSettings.js");
\Page\Page::addCSS($this->path . "css/itemViewSettings.css");

$this->setData(array(
    "inheritType"           => $inheritType,
    "obEntity"              => $obEntity,
    "arEntityFields"        => $arEntityFields,
    "arEntityView"          => $arEntityView,
    "listItemURL"           => $listItemURL,
    "editItemURL"           => $editItemURL
));

$this->includeView();
?>

<?php 

?>

<?php 

?>