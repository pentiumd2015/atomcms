<?
$coreDir = CModule::getModuleDir("core");

CAutoload::addDirMap(array(
    $coreDir . "/helpers",
    $coreDir . "/view"
));

CAutoload::addClassMap(array(
    "CAdminApplication" => "admin/CAdminApplication.php",
    "CAdminRouter"      => "admin/CAdminRouter.php",
));

if(!function_exists("app")){
    function app($property = false){
        if(($obApp = CStorage::get("app")) && is_object($obApp)){
            return $obApp->app($property);
        }
    }
}
?>