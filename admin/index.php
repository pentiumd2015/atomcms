<?
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
//ini_set("log_errors", true);
ini_set("display_errors", true);
//ini_set("error_log", $_SERVER["DOCUMENT_ROOT"] . "/log.log");

//добавить больше проверок сервера
if(version_compare(PHP_VERSION, "5.4", "<")){
	exit("PHP needs 5.4 or higher");
}

$baseURL = str_replace("\\", "/", dirname($_SERVER["PHP_SELF"]));

if($baseURL != "/"){
    $baseURL.= "/";
}

define("ROOT_PATH", __DIR__);
define("BASE_URL", $baseURL);

require_once(ROOT_PATH . "/../modules/core/CAutoload.php");

CAutoload::getInstance()->addDirMap([
    "/modules",
    "/modules/core"
]);

$config = include(ROOT_PATH . "/config/config.php");

(new Admin\CApplication($config))->run();