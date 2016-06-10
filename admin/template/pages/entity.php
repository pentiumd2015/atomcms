<?
$obRoute                = $this->app("route");

$editEntityURL          = "/admin/settings/entity/";

$listEntityFieldURL     = "/admin/settings/entities/{ID}/fields/";
$addEntityFieldURL      = "/admin/settings/entities/{ID}/fields/add/";
$editEntityFieldURL     = "/admin/settings/entities/{ID}/fields/{FIELD_ID}/";

$listEntityAccessURL    = "/admin/settings/entities/{ID}/access/";

switch($obRoute->path){
    case $editEntityURL:
        CWidget::render("entity", "index", "index", array(
            "editURL"   => $editEntityURL
        ));
        break;
    case $listEntityFieldURL:
    case $addEntityFieldURL:
    case $editEntityFieldURL:
        \Page\Breadcrumbs::add(array(
            $listEntityURL => "Список сущностей"
        ));
        
        CWidget::render("entity.field", "index", "index", array(
            "entityURL" => $editEntityURL,
            "editURL"   => $editEntityFieldURL,
            "addURL"    => $addEntityFieldURL,
            "listURL"   => $listEntityFieldURL,
            "entityID"  => $obRoute->getVarValue("ID"),
        ));
        break;
    case $listEntityAccessURL:
        \Page\Breadcrumbs::add(array(
            $listEntityURL => "Список сущностей"
        ));
        
        CWidget::render("entity.access", "index", "index", array(
            "entityURL" => $editEntityURL,
            "listURL"   => $listEntityAccessURL,
            "entityID"  => $obRoute->getVarValue("ID"),
        ));
        break;
}
?>