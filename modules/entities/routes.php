<?
return array(
    "/admin/entity_items/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
    ),
    "/admin/entity_sections/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
    ),
    "/admin/entity_sections/{ENTITY_ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID"    => "^\d+$"
        )
    ),
    "/admin/entity_sections/{ENTITY_ID}/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID"    => "^\d+$"
        )
    ),
    "/admin/entity_sections/{ENTITY_ID}/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID"    => "^\d+$",
            "ID" => "^\d+$"
        )
    ),
    "/admin/entity_elements/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
    ),
    "/admin/entity_elements/{ENTITY_ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID"    => "^\d+$"
        )
    ),
    "/admin/entity_elements/{ENTITY_ID}/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID"    => "^\d+$"
        )
    ),
    "/admin/entity_elements/{ENTITY_ID}/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entityItems.php",
        "varParams" => array(
            "ENTITY_ID" => "^\d+$",
            "ID"        => "^\d+$"
        )
    ),
    
    
    
    
    
    
    
    
    "/admin/settings/entity_groups/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
    ),
    "/admin/settings/entity_groups/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php"
    ),
    "/admin/settings/entity_groups/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
    ),
    "/admin/settings/entities/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
    ),
    "/admin/settings/entities/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/fields/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/fields/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/fields/{FIELD_ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$",
            "FIELD_ID"  => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/display/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/display/edit/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/signature/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
    "/admin/settings/entities/{ID}/access/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
);
?>