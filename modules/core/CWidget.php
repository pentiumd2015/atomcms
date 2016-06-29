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
    protected $config = [];
    
    public function __construct(array $config = []){
        $this->config   = $config;
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
    
    public function getParam($paramName, $ifNull = null){
        return isset($this->params[$paramName]) ? $this->params[$paramName] : $ifNull ;
    }
    
    public function setParam($paramName, $value){
        $this->params[$paramName] = $value;
        
        return $this;
    }
    
    protected function getViewFilePath($viewName = null){
        $viewFile = $viewName ? CFile::normalizePath($this->path . $this->config["viewPath"] . "/" . $viewName) : null ;
        
        if($viewFile && substr($viewFile, -4) != ".php"){
            $viewFile.= ".php";
        }
        
        return $viewFile;
    }
    
    protected function getControllerFilePath($controllerName = NULL){
        $controllerFile = $controllerName ? CFile::normalizePath($this->path . $this->config["controllerPath"] . "/" . $controllerName) : null ;
        
        if($controllerFile && substr($controllerFile, -4) != ".php"){
            $controllerFile.= ".php";
        }
        
        return $controllerFile;
    }
    
    public function includeView($viewName = null, $returnBody = false){
        if($viewName !== null){
            $this->viewName = $viewName;
            $this->viewFile = $this->getViewFilePath($this->viewName);
        }

        if($this->viewFile){
            if(substr($this->viewFile, -4) != ".php"){
                $this->viewFile.= ".php";
            }

            if(is_file(ROOT_PATH . $this->viewFile)){
                $this->content = $this->renderFile(ROOT_PATH . $this->viewFile, $this->getViewData());
                
                CEvent::trigger("CORE.WIDGET.VIEW.RENDER", [$this]);
                
                if(!$returnBody){
                    if($this->content !== null){
                        echo $this->content;
                    }
                }else{
                    return $this->content;
                }
            }else{
                CEvent::trigger("CORE.WIDGET.VIEW.NOT_FOUND", [$this]);
            }
        }
    }

    public function run($name, $controllerName = "index", $viewName = null, array $params = []){
        $this->name             = $name;
        $this->controllerName   = $controllerName;
        $this->viewName         = $viewName;
        $this->params           = $params;
        $this->path             = CFile::normalizePath("/" . $this->config["path"] . "/" . $this->name . "/");
        $this->controllerFile   = $this->getControllerFilePath($this->controllerName);
        $this->viewFile         = $this->getViewFilePath($this->viewName);
        
        CEvent::trigger("CORE.WIDGET.BEFORE", [$this]);

        if(is_file(ROOT_PATH . $this->controllerFile)){
            $this->result = require(ROOT_PATH . $this->controllerFile);
        }else{
            CEvent::trigger("CORE.WIDGET.NOT_FOUND", [$this]);
        }
        
        CEvent::trigger("CORE.WIDGET.AFTER", [$this]);

        return $this->result;
    }
    
    public static function render($name, $controllerName = "index", $viewName = false, array $params = []){
        $config = CAtom::$app->config["widget"];
        $module = new static($config);
        return $module->run($name, $controllerName, $viewName, $params);
    }

    protected function renderFile($__file, array $__data = []){
        ob_start();
        ob_implicit_flush(false);
        extract($__data, EXTR_OVERWRITE);
        require($__file);
        return ob_get_clean();
    }
}