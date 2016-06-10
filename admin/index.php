<?
if(version_compare(PHP_VERSION, '5.3.10', '<')){
	exit('PHP needs 5.3.10 or higher');
}

$baseURL = str_replace('\\', '/', dirname($_SERVER["PHP_SELF"]));

if($baseURL != "/"){
    $baseURL.= "/";
}

define("ADMIN_ROOT_PATH", __DIR__);
define("ROOT_PATH", dirname(ADMIN_ROOT_PATH));
define("BASE_URL", $baseURL);
define("MODULE_PATH", ROOT_PATH . '/modules');
define("CORE_PATH", MODULE_PATH . '/core');

require_once(ROOT_PATH . "/modules/core/CAutoload.php");

CAutoload::init();

CAutoload::addDirMap(array(
    "/modules",
    "/modules/core"
));



$configFile = __DIR__ . "/config/config.php";

$arConfig = include($configFile);

CModule::setConfig($arConfig["module"]);

if(CModule::load("core.admin")){
    $obApp = CAdminApplication::getInstance();
    
    $obApp->setConfig($arConfig);
    $obApp->process();
}




//require_once(ROOT_PATH . "/modules/core/admin/admin.autoload.php");


?>

<?php 

?>

<?php 

?>