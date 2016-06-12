<?
$obRoute            = CAtom::$app->route;

$listGroupAccessURL = "/admin/user_group_access/";
$addGroupAccessURL  = "/admin/user_group_access/add/";
$editGroupAccessURL = "/admin/user_group_access/{ID}/";

$listGroupURL       = "/admin/user_groups/";
$addGroupURL        = "/admin/user_groups/add/";
$editGroupURL       = "/admin/user_groups/{ID}/";

$listUserURL        = "/admin/users/";
$addUserURL         = "/admin/users/add/";
$editUserURL        = "/admin/users/{ID}/";

switch($obRoute->path){
    case $listGroupAccessURL:
    case $addGroupAccessURL:
    case $editGroupAccessURL:
        CWidget::run("user.group.access", "index", "index", array(
            "editURL"   => $editGroupAccessURL,
            "addURL"    => $addGroupAccessURL,
            "listURL"   => $listGroupAccessURL
        ));
        break;
    case $listGroupURL:
    case $addGroupURL:
    case $editGroupURL:
        CWidget::run("user.group", "index", "index", array(
            "editURL"   => $editGroupURL,
            "addURL"    => $addGroupURL,
            "listURL"   => $listGroupURL
        ));
        break;
    case $listUserURL:
    case $addUserURL:
    case $editUserURL:
        CWidget::run("user", "index", "index", array(
            "editURL"   => $editUserURL,
            "addURL"    => $addUserURL,
            "listURL"   => $listUserURL
        ));
        break;
}

?>