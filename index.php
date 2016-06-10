<?
if(version_compare(PHP_VERSION, '5.3.10', '<')){
	exit('PHP needs 5.3.10 or higher');
}

$baseURL = str_replace('\\', '/', dirname($_SERVER["PHP_SELF"]));

if($baseURL != "/"){
    $baseURL.= "/";
}

define("ROOT_PATH", __DIR__);
define("BASE_URL", $baseURL);
define("MODULE_PATH", ROOT_PATH . '/modules');
define("CORE_PATH", MODULE_PATH . '/core');

require_once(CORE_PATH . '/CAutoload.php');

CAutoload::init();

CAutoload::add(array(
    MODULE_PATH,
    CORE_PATH
));

if(CModule::load("core.application")){
    Application\CApplication::getInstance()->process();
}else{
    CTools::p(CModule::getErrors());
    exit;
}