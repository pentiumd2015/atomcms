<?
return array(
    "errors" => array(
        "logFile"          => "error.log",
        "displayErrors"    => true,
        "logErrors"        => false,
        "errorTypes"       => E_ALL ^ E_NOTICE
    ),
    "template" => array(
        "path"              => "/template/",
        "pagePath"          => "pages/",
        "layoutPath"        => "layouts/",
        "blockPath"         => "blocks/"
    ),
    "widget" => array(
        "path"              => "/widgets/",
        "controllerPath"    => "controller/",
        "viewPath"          => "view/"    
    ),
    "module" => array(
        "path"              => "/modules",
        "autoloadFile"      => "admin.autoload.php"
    ),
    "eventFile"             => "/events/core.php",
    "routeFile"             => "/config/route.php",
    "modules"               => include(__DIR__ . "/modules.php"),
    "routes"                => include(__DIR__ . "/routes.php"),
    "db" => array(
        "dsn"       => "mysql:host=localhost;dbname=cms",
        "username"  => "root",
        "password"  => "",
        "attributes"=> array(
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND    => "SET NAMES UTF8"
        )
    )
);