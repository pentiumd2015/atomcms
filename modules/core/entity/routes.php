<?
return array(
    "/admin/settings/entity/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
    ),
    
    
    
    /*"/admin/settings/entity/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
    ),
    "/admin/settings/entity/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),*/
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
    "/admin/settings/entities/{ID}/access/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "entity.php",
        "varParams" => array(
            "ID"        => "^\d+$"
        )
    ),
);
?>