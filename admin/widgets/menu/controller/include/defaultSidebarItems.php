<?
return array(
    array(
        "icon"      => "icon-users",
        "priority"  => 999,
        "title"     => "Пользователи",
        "alias"     => "users",
        "items"     => array(
            array(
                "priority"      => 997,
                "title"         => "Пользователи",
                "alias"         => "user.list",
                "link"          => BASE_URL . "users/",
                "extraLinks"    => array(BASE_URL . "users/.*"),
            ),
            array(
                "priority"      => 998,
                "title"         => "Группы пользователей",
                "alias"         => "user.groups",
                "link"          => BASE_URL . "user_groups/",
                "extraLinks"    => array(BASE_URL . "user_groups/.*"),
            ),
          /*  array(
                "priority"      => 999,
                "title"         => "Правила доступа",
                "alias"         => "user.group.access",
                "link"          => BASE_URL . "user_group_access/",
                "extraLinks"    => array(BASE_URL . "user_group_access/.*"),
            )*/
        )
    ),
    array(
        "icon"      => "icon-cogs",
        "priority"  => 1000,
        "title"     => "Настройки",
        "alias"     => "settings",
        /*"items"     => array(
            array(
                "priority"      => 997,
                "title"         => "Сайты",
                "alias"         => "settings.site.list",
                "link"          => BASE_URL . "settings/site/",
                "extraLinks"    => array(BASE_URL . "settings/site/.*"),
            ),
            array(
                "priority"      => 998,
                "title"         => "Шаблоны",
                "alias"         => "settings.site.template.list",
                "link"          => BASE_URL . "settings/site_template/",
                "extraLinks"    => array(BASE_URL . "settings/site_template/.*"),
            ),
            array(
                "icon"          => "icon-tree2",
                "priority"      => 999,
                "title"         => "Дерево сайта",
                "alias"         => "settings.site.tree",
                "link"          => BASE_URL . "settings/site_tree/",
                "extraLinks"    => array(BASE_URL . "settings/site_tree/.*"),
            ),
        )*/
    )
);
?>