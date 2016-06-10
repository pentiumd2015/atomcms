<?
include(ROOT_PATH . $this->path . "classes/SidebarMenu.php");

$obMenu = new SidebarMenu($this->app("route")->url);

CPage::addJS($this->path . "js/script.js");
CPage::addCSS($this->path . "css/style.css");

$defaultSidebarFile = __DIR__ . "/include/defaultSidebarItems.php";

if(is_file($defaultSidebarFile) && ($arDefaultSidebarItems = include($defaultSidebarFile)) && is_array($arDefaultSidebarItems)){
    $obMenu->setItems($arDefaultSidebarItems);
}

CEvent::trigger("MENU.SIDEBAR.BEFORE.RENDER", array($obMenu));

$this->setData(array(
    "obMenu"    => $obMenu
));

$this->includeView();

CEvent::trigger("MENU.SIDEBAR.AFTER.RENDER", array($obMenu));
?>