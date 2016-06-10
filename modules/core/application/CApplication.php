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

class CApplication extends CBaseApplication{
    protected $config = array();
    protected $site;
    protected $router;
    protected $route;
    protected $template;
    protected $db;
    
    static protected $configFile = "/config/config.php";
    
    
    public function __construct(){
        if(is_file(ROOT_PATH . static::$configFile) && ($arConfig = include(ROOT_PATH . static::$configFile)) && is_array($arConfig)){
            $this->config = $arConfig;
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    protected $eventPath        = "/events/core.php";
    protected $configPath       = "/config/config.php";
    protected $routePath        = "/config/route.php";
    protected $modulePath       = "/config/module.php";
    protected $templatePath     = "/config/template.php";
    protected $sitePath         = "/config/site.php";
    
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
    
    public function process(){
        CStorage::set("app", $this);
        
        $this->configProcess();
        
        CWidget::setConfig($this->config["widget"]);
        
        $this->errorProcess();
        
        $this->eventProcess();

        
        $this->db = \DB\Connection::getInstance($this->config["db"]["dsn"], $this->config["db"]["username"], $this->config["db"]["password"]);
        
        if(isset($this->config["db"]["attributes"])){
            $this->db->setAttributes($this->config["db"]["attributes"]);
        }
        
        CSession::start();
        
        $this->user = new \Models\User;
        
        $this->user->initCurrent();
        
        CEvent::trigger("CORE.START");
                
        $this->siteProcess();

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
    
    
    protected function siteProcess($host = NULL){
        if(!$host){
            $host = $_SERVER["HTTP_HOST"];
        }
        
        $siteFile   = CFile::normalizePath(ROOT_PATH . "/" . BASE_URL . $this->sitePath);
        
        $siteFound  = false;

        if(is_file($siteFile) && ($arSites = include($siteFile)) && is_array($arSites)){
            foreach($arSites AS $siteID => $arSite){
                if((is_string($arSite["domains"]) && ($host == $arSite["domains"] || $arSite["domains"] == "*")) || 
                   (is_array($arSite["domains"]) && in_array($host, $arSite["domains"]))){
                    $arSite["siteID"]   = $siteID;
                    $this->site         = new \CSite($arSite);
                    
                    if(isset($arSite["active"]) || !$arSite["active"]){
                        CEvent::trigger("CORE.SITE.NOT_ACTIVE", array($this, $this->site, $host));
                        
                        throw new CException("Сайт с доменом [" . $host . "] неактивен");
                    }
                    
                    $siteFound = true;
                    
                    break;
                }
            }
        }
        
        if(!$siteFound){
            CEvent::trigger("CORE.SITE.NOT_FOUND", array($this, $host));
            
            throw new CException("Приложение не может найти сайт с доменом [" . $host . "]");
        }
    }
    
    protected function routerProcess($route = NULL){
        if(!$route){
            $route = ($_SERVER["REQUEST_URI"]);
                
            $route = "/" . ltrim($route, "/");
            
            $pos = strpos($route, "?");
            
            if($pos !== false){
                $route = substr($route, 0, $pos);
            }
            
            if(empty($route)){
                $route = "/";
            }
        }
        
        /*get routes from file*/
        $routeFile = CFile::normalizePath(ROOT_PATH . "/" . BASE_URL . $this->routePath);
        
        $arRoutes = array();
        
        if(is_file($routeFile) && ($arRoutes = include($routeFile)) && is_array($arRoutes)){
            if(isset($arRoutes[$this->site->siteID]) && is_array($arRoutes[$this->site->siteID])){
                $arRoutes = $arRoutes[$this->site->siteID];
            }
        }
        /*get routes from file*/
        
        $this->router = new \CRouter($arRoutes);
        
        foreach($arRoutes AS $path => &$arRouteItem){
            $arRouteItem["path"] = $path;
        }
        
        unset($arRouteItem);
        
        $arRoutes = CArrayFilter::filter($arRoutes, function($arItem){
            return !isset($arItem["active"]) || $arItem["active"] == 1;
        });
        
        $arRoutes = CArraySorter::subSort($arRoutes, function($a, $b){
            return -1 * strnatcasecmp($a["path"], $b["path"]);
        });
                  
        $routeFound = false;

        foreach($arRoutes AS $arRoute){
            $tempPath   = $arRoute["path"];
            $tempRoute  = $route;                
            $pathLength = strlen($tempPath);
            
            if(substr($tempRoute, 0, $pathLength) == $tempPath){//проверяем совпадает ли урл
                $routeFound = true;
            }else if($arRoute["slashConsider"] == false){ //если не надо учитывать слеш на конце урл строки
                if($tempRoute != "/"){
                    $tempRoute = rtrim($tempRoute, "/");
                }
                
                if($tempPath != "/"){
                    $tempPath = rtrim($tempPath, "/");
                }
                
                if(substr($tempRoute, 0, $pathLength) == $tempPath){ //обрезаем слеши справа и снова сравниваем
                    $routeFound = true;
                }
            }
            
            if($routeFound){
                $arRoute["path"]    = urldecode($arRoute["path"]);
                $arRoute["url"]     = urldecode($route);
                $arRoute["sefUri"]  = substr($arRoute["url"], strlen($arRoute["path"]));
                
                if($arRoute["slashConsider"] == false){
                    $arRoute["sefUri"] = rtrim($arRoute["sefUri"], "/");
                }
                
                $this->route = new \CRoute($arRoute);
                
                break;
            }
        }
        
        if(!$routeFound){
            CEvent::trigger("CORE.ROUTE.NOT_FOUND", array($this, $route));
            
            throw new \CException("Приложение не может определить путь [" . $route . "]");
        }
        
        CEvent::trigger("CORE.ROUTE.BEFORE", array($this, $this->router, $this->route));

        if($this->route->sefUri){
            if(isset($this->route->sefParams)){
                $sefFound = false;
                
                if(is_array($this->route->sefParams)){
                    $arSefParams = CArrayFilter::filter($this->route->sefParams, function($arItem){
                        return !isset($arItem["active"]) || $arItem["active"] == 1;
                    });
                    
                    $arSefParams = CArraySorter::subSort($arSefParams, "priority", SORT_ASC);
    
                    foreach($arSefParams AS $pattern => $arSefParam){
                        if($this->route->slashConsider == false){
                            if($pattern != "/"){
                                $pattern = rtrim($pattern, "/");
                            }
                        }
                        
                        if(!isset($arSefParam["params"])){
                            $arSefParam["params"] = array();
                        }
                        
                        $arSefValues = array();
                        
                        if(\CRoute::checkMatchParams($this->route->sefUri, $pattern, $arSefValues, $arSefParam["params"])){
                            $arSefParam["pattern"]  = $pattern;
                            $arSefParam["values"]   = $arSefValues;
                            
                            $this->route->sefValues = $arSefParam;
                            
                            CEvent::trigger("CORE.ROUTE.SEF.START", array($this, $this->router, $this->route));
    
                            $sefFound = true;
                            
                            break;                                                        
                                                    
                        }
                    }
                }else if($this->route->sefParams == "*"){
                    $this->route->sefValues = array(
                        "pattern" => "*"
                    );
                    
                    CEvent::trigger("CORE.ROUTE.SEF.START", array($this, $this->router, $this->route));
    
                    $sefFound = true;
                }
                
                if(!$sefFound){
                    CEvent::trigger("CORE.ROUTE.SEF.NOT_FOUND", array($this, $this->router, $this->route));
                    
                    throw new CException("Приложение не может определить чпу путь [" . $this->route->sefUri . "] для роута [" . $this->route->path . "]");
                }
            }else{
                CEvent::trigger("CORE.ROUTE.NOT_FOUND", array($this, $this->router, $this->route));
                
                throw new CException("Приложение не может определить чпу путь [" . $this->route->sefUri . "] для роута [" . $this->route->path . "]");
            }
        }

        CEvent::trigger("CORE.ROUTE.AFTER", array($this, $this->router, $this->route));
    }
    
    protected function templateProcess(){
        $arTempTemplateParams = array();
        
        if(isset($this->route->sefValues, $this->route->sefValues["templates"]) && is_array($this->route->sefValues["templates"])){
            $arTempTemplateParams = $this->route->sefValues["templates"];
        }else if(isset($this->route->templates) && is_array($this->route->templates)){
            $arTempTemplateParams = $this->route->templates;
        }

        if(count($arTempTemplateParams)){
            $arTempTemplateParams = \Helpers\CArrayFilter::filter($arTempTemplateParams, function($arItem){
                return (!isset($arItem["active"]) || $arItem["active"] == 1);
            });
            
            $arTempTemplateParams = \Helpers\CArraySorter::subSort($arTempTemplateParams, "priority", SORT_ASC);

            $templateFound = false;
            
            $absoluteTemplatePath = CFile::normalizePath(ROOT_PATH . "/" . $this->config["template"]["path"]);

            foreach($arTempTemplateParams AS $arTemplateItem){
                $templateAlias  = $arTemplateItem["template"];                  
                $templateFile   = CFile::normalizePath(ROOT_PATH . "/" . BASE_URL . $this->templatePath);                    
                
                if(is_file($templateFile) && ($arTemplate = include($templateFile)) && is_array($arTemplate) && isset($arTemplate[$templateAlias])){
                    $arTemplate[$templateAlias]["alias"]    = $templateAlias;
                    $arTemplateItem["info"]                 = $arTemplate[$templateAlias];
                    
                    if(!isset($arTemplateItem["condition"])){
                        $templateFound  = true;
                        
                        break;
                    }
                    
                    if(is_callable($arTemplateItem["condition"])){
                        if(call_user_func_array($arTemplateItem["condition"], array($this, $arTemplateItem))){
                            $templateFound  = true;
                            
                            break;
                        }
                    }else{
                        $conditionFile = CFile::normalizePath($absoluteTemplatePath . "/" . $arTemplateItem["info"]["path"] . "/" . $this->config["template"]["pagePath"] . "/conditions/" . $arTemplateItem["condition"]);

                        if(is_file($conditionFile) && ($result = include($conditionFile)) == true){
                            $templateFound  = true;
                            
                            break;
                        }
                    }
                }
            }
            
            if(!$templateFound){
                CEvent::trigger("CORE.TEMPLATE.NOT_FOUND", array($this, $this->router, $this->route));
                
                throw new CException("Приложение не может определить шаблон для роута [" . $this->route->path . "]");
            }else{
                $templatePath = CFile::normalizePath($this->config["template"]["path"] . "/" . $arTemplateItem["info"]["path"]);

                \Template\CTemplate::setConfig(array(
                    "path" => $templatePath
                ) + $this->config["template"]);
                
                \Template\CBlock::setConfig(array(
                    "path" => CFile::normalizePath($templatePath . "/" . $this->config["template"]["blockPath"])
                ));
                
                $this->template = new \Template\CTemplate;
                $this->template->params = $arTemplateItem;                                        
                
                CEvent::trigger("CORE.TEMPLATE.BEFORE", array($this, $this->template));
                
                $this->template->process($this->template->params["layoutFile"], $this->template->params["pageFile"]);
                
                /*deferred events*/
                CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.BEFORE", array($this, $this->template));
                
                $this->template->content = \View\CDynamicContent::process($this->template->content);
                
                CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.AFTER", array($this, $this->template));
                /*deferred events*/
                
                CEvent::trigger("CORE.TEMPLATE.AFTER", array($this, $this->template));
            }
        }else if($this->route->widget && is_array($this->route->widget)){
            call_user_func_array("\CWidget::render", $this->route->widget);
        }else{
            CEvent::trigger("CORE.TEMPLATE_OR_MODULE.NOT_FOUND", array($this, $this->router, $this->route));
            
            throw new CException("Приложение не может определить шаблон для роута [" . $this->route->path . "]");
        }
    }
}
?>