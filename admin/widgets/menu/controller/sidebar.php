<?
include(dirname(__DIR__) . "/classes/SidebarMenu.php");

$this->view->addJs(BASE_URL . $this->path . "js/script.js");
$this->view->addCss(BASE_URL . $this->path . "css/style.css");

$obMenu = new SidebarMenu(CAtom::$app->route->url);

$defaultSidebarFile = __DIR__ . "/include/defaultSidebarItems.php";

if(is_file($defaultSidebarFile) && ($arDefaultSidebarItems = include($defaultSidebarFile)) && is_array($arDefaultSidebarItems)){
    $obMenu->setItems($arDefaultSidebarItems);
}

CEvent::trigger("MENU.SIDEBAR.BEFORE.RENDER", array($obMenu));

$this->setViewData(array(
    "obMenu" => $obMenu
));

$this->includeView();

CEvent::trigger("MENU.SIDEBAR.AFTER.RENDER", array($obMenu));