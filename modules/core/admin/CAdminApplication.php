<?
use \DB\Connection;

class CAdminApplication extends CConstruct{
    public $config;
    public $router;
    public $route;
    public $template;
    public $db;
    public $user;
    
    public function getConfig(){
        return $this->config;
    }
    
    public function setConfig($arConfig){
        $this->config = $arConfig;
        
        return $this;
    }
    
    public function process(){
        CStorage::set("app", $this);
        
        /*init config*/
        if(!$this->config){
            $configFile = CFile::normalizePath(ROOT_PATH . BASE_URL . "config/config.php");
            $arConfig   = CFile::getArrayFromFile($configFile);
            
            if(!count($arConfig)){
                $arConfig = array(                    
                    "errors" => array(
                        "logFile"          => "error.log",
                        "displayErrors"    => true,
                        "logErrors"        => false,
                        "errorTypes"       => E_ALL ^ E_NOTICE
                    ),
                    "template" => array(
                        "pagePath"          => "pages/",
                        "path"              => BASE_URL . "template/",
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
                );
            }
            
            $this->config = $arConfig;
        }
        /*init config*/
        
        CWidget::setConfig($this->config["widget"]);

        $this->errorProcess();
        
        /*init events*/
        $eventFile = CFile::normalizePath(ROOT_PATH . "/" . $this->config["eventFile"]);

        if(is_file($eventFile)){
            include($eventFile);
        }
        /*init events*/
   
        /*init db*/
        $this->db = Connection::getInstance($this->config["db"]["dsn"], $this->config["db"]["username"], $this->config["db"]["password"]);
       
        if(isset($this->config["db"]["attributes"])){
            $this->db->setAttributes($this->config["db"]["attributes"]);
        }
        /*init db*/
        
        CSession::start();
        
        $this->router   = new CAdminRouter;
        $this->user     = new CUser;
        
        $this->user->initCurrent();

        CEvent::trigger("CORE.START", array($this));

        $this->moduleProcess();
        
        $this->routerProcess();
        
        $this->templateProcess();
      
        CEvent::trigger("CORE.CONTENT.BEFORE", array($this));

        /**
         * Вывод конечного контента
         */
        echo $this->template->content;

        CEvent::trigger("CORE.END", array($this));
    }
    
    protected function errorProcess(){
        $arConfig = $this->config["errors"];
        
        ini_set("error_reporting", $arConfig["errorTypes"]);
        ini_set("display_errors" , $arConfig["displayErrors"]);
        ini_set("log_errors"     , $arConfig["logErrors"]);
        
        $errorLogFile = CFile::normalizePath(ROOT_PATH . "/" . $arConfig["logFile"]);
        
        ini_set("error_log", $errorLogFile);
        error_reporting($arConfig["errorTypes"]);
        
        set_exception_handler("CException::exception");
        
        $obApp = $this;
        
        register_shutdown_function(
            function($errorType) use ($obApp){
                CEvent::trigger("CORE.SHUTDOWN", array($obApp, $errorType));
            }, 
            $arConfig["errorTypes"]
        );
    }
    
    protected function moduleProcess(){
        $moduleFile = CFile::normalizePath(ROOT_PATH . "/" . $this->config["moduleFile"]);
        $arModules  = CFile::getArrayFromFile($moduleFile);
        
        $this->arAutoloadModules = CArrayFilter::filter($arModules, function($arItem){
            return !isset($arItem["active"]) || $arItem["active"] == 1;
        });
        
        $this->arAutoloadModules = CArraySorter::subSort($this->arAutoloadModules, "priority", SORT_ASC);

        CModule::load(array_keys($this->arAutoloadModules));
    }
    
    protected function routerProcess($route = NULL){
        if(!$route){
            $route = "/" . ltrim($_SERVER["REQUEST_URI"], "/");
            
            if(($pos = strpos($route, "?")) !== false){
                $route = substr($route, 0, $pos);
            }
            
            if(!strlen($route)){
                $route = "/";
            }
        }

        /*get routes from file*/
        $routeFile  = CFile::normalizePath(ROOT_PATH . "/" . $this->config["routeFile"]);
        $arRoutes   = CFile::getArrayFromFile($routeFile);
        $this->router->addRoutes($arRoutes);
        /*get routes from file*/
        
        CEvent::trigger("CORE.ROUTER.BEFORE", array($this, $this->router));
        
        $arRoutes = CArrayFilter::filter($this->router->getRoutes(), function($arItem){
            return !isset($arItem["active"]) || $arItem["active"] == 1;
        });
        
        $routeFound     = false;
        $arVarValues    = array();
        
        foreach($arRoutes AS $arRoute){
            $arVarParams = is_array($arRoute["varParams"]) ? $arRoute["varParams"] : array() ;
            
            if(CRoute::checkMatchParams($route, $arRoute["path"], $arVarValues, $arVarParams)){
                $arRoute["path"]        = urldecode($arRoute["path"]);
                $arRoute["url"]         = urldecode($route);
                $arRoute["varValues"]   = $arVarValues;
                
                $this->route            = new CRoute($arRoute);
                $routeFound             = true;
                
                break;
            }
        }
        
        if(!$routeFound){
            CEvent::trigger("CORE.ROUTE.NOT_FOUND", array($this, $route));
            
            throw new CException("Приложение не может определить путь [" . $route . "]");
        }

        CEvent::trigger("CORE.ROUTER.AFTER", array($this, $this->router, $this->route));
    }
    
    protected function templateProcess(){
        CTemplate::setConfig(array(
            "path" => $this->config["template"]["path"]
        ) + $this->config["template"]);
        
        CBlock::setConfig(array(
            "path" => CFile::normalizePath($this->config["template"]["path"] . "/" . $this->config["template"]["blockPath"])
        ));

        $this->template = new CTemplate;                                      
        
        CEvent::trigger("CORE.TEMPLATE.BEFORE", array($this, $this->template));
        
        $this->template->process($this->route->layoutFile, $this->route->pageFile);
        
        /*deferred events*/
        CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.BEFORE", array($this, $this->template));
        
        $this->template->content = CDynamicContent::process($this->template->content);
        
        CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.AFTER", array($this, $this->template));
        /*deferred events*/
        
        CEvent::trigger("CORE.TEMPLATE.AFTER", array($this, $this->template));
    }
}
?>