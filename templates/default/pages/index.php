<?
$obRoute    = $this->app("route");
$alias      = $obRoute->getAlias();
echo $alias;
if($alias == "entity.view"){echo 123;
    CWidget::render("entity.view", "index", "index", array(
        "listURL"   => "/settings/entities/view/"
    ));
}else if(in_array($alias, array("entity.list", "entity.add", "entity.edit"))){
    CWidget::render("entity", "index", "index", array(
        "editURL"   => "/settings/entities/{ENTITY_ID}/",
        "addURL"    => "/settings/entities/add/",,
        "listURL"   => "/settings/entities/",
        "mode"      => $alias
    ));
}else if(in_array($alias, array("entity.field.list", "entity.field.add", "entity.field.edit"))){
    CWidget::render("entity.field", "index", "index", array(
        "entityURL" => "/settings/entities/{ENTITY_ID}/",
        "editURL"   => "/settings/entities/{ENTITY_ID}/fields/{FIELD_ID}/",
        "addURL"    => "/settings/entities/{ENTITY_ID}/fields/add/",
        "listURL"   => "/settings/entities/{ENTITY_ID}/fields/",
        "mode"      => $alias,
        "entityID"  => (int)$obRoute->getUrlValue("ENTITY_ID")
    ));
}else if($alias == "entity.signature"){
    CWidget::render("entity.signature", "index", "index", array(
        "entityURL" => "/settings/entities/{ENTITY_ID}/",
        "listURL"   => "/settings/entities/{ENTITY_ID}/signature/",
        "mode"      => $alias,
        "entityID"  => (int)$obRoute->getUrlValue("ENTITY_ID")
    ));
}else if($alias == "entity.access"){
    CWidget::render("entity.access", "index", "index", array(
        "entityURL" => "/settings/entities/{ENTITY_ID}/",
        "listURL"   => "/settings/entities/{ENTITY_ID}/access/",
        "mode"      => $alias,
        "entityID"  => (int)$obRoute->getUrlValue("ENTITY_ID")
    ));
}
?>

<?php 

?>

<?php 

?>