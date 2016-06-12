<?

use Helpers\CFile;

class CWidget{
    public $path;
    public $controllerName;
    public $controllerFile;
    public $viewName;
    public $viewFile;
    public $name;
    public $params = [];
    public $content = null;
    public $result = null;
    public $view;
    protected $data = [];
    
    protected static $mainConfig = [];
    
    protected $config = [];
    
    public static function setConfig(array $config = []){
        return self::$mainConfig = $config;
    }
    
    public static function getConfig(){
        return self::$mainConfig;
    }
    
    public function __construct(array $config = []){
        $this->config   = array_merge(self::getConfig(), $config);
        $this->view     = CAtom::$app->view;
    }
    
    public function setViewData(array $data = []){
        $this->data = $data;
        
        return $this;
    }
    
    public function getViewData(){
        return $this->data;
    }
    
    public function getParams(){
        return $this->params;
    }
    
    public function getParam($paramName){
        return isset($this->params[$paramName]) ? $this->params[$paramName] : null ;
    }
    
    public function includeView($viewName = null){
        if($viewName !== null){
            if(substr($viewName, -4) != ".php"){
                $viewName.= ".php";
            }
            
            $this->viewFile = CFile::normalizePath($this->viewPath . "/" . $viewName);
        }
        
        if($this->viewFile){
            if(is_file(ROOT_PATH . $this->viewFile)){
                $this->view->widget = $this;
                echo $this->view->render(ROOT_PATH . $this->viewFile, $this->getViewData());
            }else{
                CEvent::trigger("CORE.WIDGET.VIEW.NOT_FOUND", [$this]);
            }
        }
    }

    public function render($name, $controllerName = "index", $viewName = null, array $params = []){
        $this->name             = $name;
        $this->controllerName   = $controllerName;
        $this->viewName         = $viewName;
        $this->params           = $params;
        $this->path             = CFile::normalizePath("/" . $this->config["path"] . "/" . $this->name . "/");
        $this->controllerFile   = CFile::normalizePath($this->path . $this->config["controllerPath"] . "/" . $this->controllerName);
        
        if(substr($this->controllerFile, -4) != ".php"){
            $this->controllerFile.= ".php";
        }
        
        $this->viewFile = $this->viewName ? CFile::normalizePath($this->path . $this->config["viewPath"] . "/" . $this->viewName) : null ;
        
        if($this->viewFile){
            if(substr($this->viewFile, -4) != ".php"){
                $this->viewFile.= ".php";
            }
        }
        
        CEvent::trigger("CORE.WIDGET.BEFORE", [$this]);

        if(is_file(ROOT_PATH . $this->controllerFile)){
            $this->result = require(ROOT_PATH . $this->controllerFile);
        }else{
            CEvent::trigger("CORE.WIDGET.NOT_FOUND", [$this]);
        }
        
        CEvent::trigger("CORE.WIDGET.AFTER", [$this]);
        
        if($this->content !== null){
            echo $this->content;
        }

        return $this->result;
    }
    
    public static function run($name, $controllerName = "index", $viewName = false, array $params = []){
        $config = self::getConfig();
        $module = new self($config);
        return $module->render($name, $controllerName, $viewName, $params);
    }
}