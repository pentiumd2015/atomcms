<?
namespace Admin;


use Helpers\CArraySorter;
use CEvent;
use CException;
use CRoute;
use CObject;

class CRouter extends CObject{
    protected $routes = [];
    protected $uri;
    
    public function __construct($uri, array $routes = []){
        $uri = "/" . ltrim($uri, "/");
        
        list($this->uri) = explode("?", $uri, 2);
        
        $this->setRoutes($routes);
    }
    
    public function addRoutes(array $routes = []){
        return $this->routes = array_merge($this->routes, $this->prepare($routes));
    }
    
    public function setRoutes(array $routes = []){
        return $this->routes = $this->prepare($routes);
    }
    
    public function getRoutes(){
        return $this->routes;
    }
    
    public function prepare($routes){
        foreach($routes AS $path => $route){
            $routes[$path]["path"] = $path;
        }
        
        return $routes;
    }
    
    public function getMatchRoute(){
        CEvent::trigger("CORE.ROUTER.BEFORE", [$this]);
        
        $routes = array_filter($this->routes, function($route){
            return !isset($route["active"]) || $route["active"] == 1;
        });
        
        $routes = CArraySorter::sort($routes, function($a, $b){
            return -1 * strnatcasecmp($a["path"], $b["path"]);
        });
        
        $routeFound = false;
        
        foreach($routes AS $routeItem){
            if(trim($routeItem["path"], "/") == trim($this->uri, "/")){ //если полностью совпадает url
                $routeItem["path"]  = urldecode($routeItem["path"]);
                $routeItem["url"]   = urldecode($routeItem["path"]);
                $routeItem["query"] = null;
                $routeFound = true;
            }else if(strpos($this->uri, $routeItem["path"]) === 0){ //если есть доплнительный uri, то передаем дальше в приложение как чпу в ключе query
                $routeItem["path"]  = urldecode($routeItem["path"]);
                $routeItem["url"]   = urldecode($this->uri);
                $routeItem["query"] = trim(substr($this->uri, strlen($routeItem["path"])), "/");
                $routeFound = true;
            }
            
            if($routeFound){
                $route = new CRoute($routeItem);
                
                CEvent::trigger("CORE.ROUTER.AFTER", [$this, $route]);

                return $route;
            }
        }
        
        CEvent::trigger("CORE.ROUTE.NOT_FOUND", [$this]);
            
        throw new CException("Приложение не может определить путь [" . $this->uri . "]");
        
        return false;
    }
}