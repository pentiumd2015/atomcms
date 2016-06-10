<?
CWidget::render("entity.item", "index", "index", array(
    "listEntityURL"     => "/admin/entity_items/",
    "listSectionURL"    => "/admin/entity_sections/{ENTITY_ID}/",
    "addSectionURL"     => "/admin/entity_sections/{ENTITY_ID}/add/",
    "editSectionURL"    => "/admin/entity_sections/{ENTITY_ID}/{ID}/",
    "listElementURL"    => "/admin/entity_elements/{ENTITY_ID}/",
    "addElementURL"     => "/admin/entity_elements/{ENTITY_ID}/add/",
    "editElementURL"    => "/admin/entity_elements/{ENTITY_ID}/{ID}/"
));
?>