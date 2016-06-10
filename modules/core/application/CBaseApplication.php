<?
namespace Application;

use \Helpers\CFile;
use \Helpers\CArrayFilter;
use \Helpers\CArraySorter;
use \CEvent;
use \CStorage;
use \CWidget;
use \CSession;
use \CModule;

class CBaseApplication extends \CConstruct{
    protected $config;
    protected $router;
    protected $route;
    
    protected $eventPath        = "/events/core.php";
    protected $routePath        = "/config/route.php";
    protected $modulePath       = "/config/module.php";
    
    public function getConfigPath(){
        return $this->configPath;
    }
    
    public function setConfigPath($configPath){
        return $this->configPath = $configPath;
    }
    
    public function getEventPath(){
        return $this->eventPath;
    }
    
    public function setEventPath($eventPath){
        return $this->eventPath = $eventPath;
    }
    
    
    
    public function getRoutePath(){
        return $this->routePath;
    }
    
    public function setRoutePath($routePath){
        return $this->routePath = $routePath;
    }
    
    public function getModulePath(){
        return $this->modulePath;
    }
    
    public function setModulePath($modulePath){
        return $this->modulePath = $modulePath;
    }
    
    public $arDefaultConfig = array(                    
        "template" => array(
            "pagePath"          => "pages/",
            "path"              => "templates/",
            "layoutPath"        => "layouts/",
            "blockPath"         => "blocks/"
        ),
        "widget" => array(
            "path"              => "widgets/",
            "controllerPath"    => "controller/",
            "viewPath"          => "view/"    
        ),
    );
    
    protected $arAutoloadModules = array();
    
    public function getRouter(){
        return $this->router;
    }
    
    public function setRouter(\CRouter $obRouter){
        $this->router = $obRouter;
        
        return $this;
    }
    
    public function getRoute(){
        return $this->route;
    }
    
    public function setRoute(\CRoute $obRoute){
        $this->route = $obRoute;
        
        return $this;
    }
    
    public function getSite(){
        return $this->site;
    }
    
    public function setSite(\CSite $obSite){
        $this->site = $obSite;
        
        return $this;
    }
    
    public function getConfig(){
        return $this->config;
    }
    
    public function setConfig($arConfig){
        $this->config = $arConfig;
        
        return $this;
    }
    
    protected function errorProcess(){
        $arConfig = $this->config["errors"];
        
        ini_set("error_reporting", $arConfig["errorTypes"]);
        ini_set("display_errors" , $arConfig["displayErrors"]);
        ini_set("log_errors"     , $arConfig["logErrors"]);
        
        $errorLogFile = CFile::normalizePath(ROOT_PATH . "/" . $arConfig["logFile"]);
        
        ini_set("error_log", $errorLogFile);
        error_reporting($arConfig["errorTypes"]);
        
        set_exception_handler("\CException::exception");
        
        $obApp = $this;
        
        register_shutdown_function(
            function($errorType) use ($obApp){
                CEvent::trigger("CORE.SHUTDOWN", array($obApp, $errorType));
            }, 
            $arConfig["errorTypes"]
        );
    }
    
    protected function moduleProcess(){
        $moduleFile = CFile::normalizePath(ROOT_PATH . "/" . BASE_URL . $this->modulePath);
        
        $arModules = array();
        
        if(is_file($moduleFile) && ($arSiteModules = include($moduleFile)) && is_array($arSiteModules) && isset($arSiteModules[$this->site->alias])){
            $arModules = $arSiteModules[$this->site->alias];
        }
        
        $this->arAutoloadModules = CArrayFilter::filter($arModules, function($arItem){
            return !isset($arItem["active"]) || $arItem["active"] == 1;
        });
        
        $this->arAutoloadModules = CArraySorter::subSort($this->arAutoloadModules, "priority", SORT_ASC);
        
        CModule::load(array_keys($this->arAutoloadModules));
    }
    
    public function end(){
        CEvent::trigger("CORE.END", array($this));
        exit;
    }
}
?>