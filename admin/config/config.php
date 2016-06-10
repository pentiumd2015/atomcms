<?
//get client site config
$obSiteApp      = new \Application\CApplication;
$arSiteConfig   = $obSiteApp->getConfig();

return array(
    "errors" => array(
        "logFile"          => "error.log",
        "displayErrors"    => true,
        "logErrors"        => false,
        "errorTypes"       => E_ALL ^ E_NOTICE
    ),
    "template" => array(
        "path"              => BASE_URL . "template/",
        "pagePath"          => "pages/",
        "layoutPath"        => "layouts/",
        "blockPath"         => "blocks/"
    ),
    "widget" => array(
        "path"              => BASE_URL . "widgets/",
        "controllerPath"    => "controller/",
        "viewPath"          => "view/"    
    ),
    "module" => array(
        "path"              => "/modules/",
        "autoloadFile"      => "admin.autoload.php"
    ),
    "eventFile"             => BASE_URL . "events/core.php",
    "routeFile"             => BASE_URL . "config/route.php",
    "moduleFile"            => BASE_URL . "config/module.php",
    "db"                    => $arSiteConfig["db"]
);
?>