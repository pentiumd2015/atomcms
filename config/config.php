<?
return array(
    "errors" => array(
        "logFile"          => "error.log",
        "displayErrors"    => true,
        "logErrors"        => false,
        "errorTypes"       => E_ALL ^ E_NOTICE
    ),
    "template" => array(
        "pagePath"          => "pages/",
        "path"              => "/templates/",
        "layoutPath"        => "layouts/",
        "blockPath"         => "blocks/"
    ),
    "widget" => array(
        "path"              => "/widgets/",
        "controllerPath"    => "controller/",
        "viewPath"          => "view/"    
    ),
    "db" => array(
        "dsn"       => "mysql:host=localhost;dbname=cb92348_cms",
        "username"  => "cb92348_cms",
        "password"  => "123456",
        "attributes"=> array(
            \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE    => \PDO::FETCH_OBJ,
            \PDO::MYSQL_ATTR_INIT_COMMAND    => "SET NAMES UTF8"
        )
    )
);
?>