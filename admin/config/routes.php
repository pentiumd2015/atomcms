<?
return array(
    "/admin/auth/" => array(
        "layoutFile" => "auth.php",
    ),
    "/admin/ajax/" => array(
        "layoutFile"    => false,
        "pageFile"      => "ajax.php",
    ),
    "/admin/404/" => array(
        "layoutFile" => "404.php",
    ),
    "/admin/user_group_access/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "users.php",
    ),
    "/admin/user_group_access/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "users.php",
    ),
    "/admin/user_group_access/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "users.php",
        "varParams" => array(
            "ID" => "^\d+$"
        )
    ),
    /*user groups*/
    "/admin/user_groups/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "user_groups.php",
    ),
    /*user groups*/
    /*users*/
    "/admin/users/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "users.php",
    ),
    /*users*/
    "/admin/settings/site/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site.php",
    ),
    "/admin/settings/site/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site.php",
    ),
    "/admin/settings/site/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site.php",
        "varParams" => array(
            "ID" => "^\w+$"
        )
    ),
    "/admin/settings/site_template/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_template.php",
    ),
    "/admin/settings/site_template/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_template.php",
    ),
    "/admin/settings/site_template/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_template.php",
        "varParams" => array(
            "ID" => "^\w+$"
        )
    ),
    "/admin/settings/site_tree/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_tree.php",
    ),
    "/admin/settings/site_tree/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_tree.php",
    ),
    "/admin/settings/site_tree/edit/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "site_tree.php",
    ),
    
    /*test*/
    "/admin/test/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "test.php",
    ),
    "/admin/test/add/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "test.php",
    ),
    "/admin/test/{ID}/" => array(
        "layoutFile"    => "index.php",
        "pageFile"      => "test.php",
        "varParams" => array(
            "ID" => "^\d+$"
        )
    ),
);
?>