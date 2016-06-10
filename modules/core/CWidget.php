<?
class CWidget extends CView{
    public $path;
    public $controllerName;
    public $controllerPath;
    public $controllerFile;
    public $viewName;
    public $viewPath;        
    public $viewFile;
    public $name;
    public $params;
    public $content;
    public $result;
    
    static protected $_arConfig = array();
    
    protected $arConfig = array();
    
    static public function setConfig($arConfig = array()){
        return self::$_arConfig = $arConfig;
    }
    
    static public function getConfig(){
        return self::$_arConfig;
    }
    
    public function __construct($arConfig = array()){
        $arDefaultConfig = self::getConfig();
        
        if(!count($arConfig)){
            $arConfig = $arDefaultConfig;
        }else{
            $arConfig+= $arDefaultConfig;
        }
        
        $this->arConfig = $arConfig;
    }
    
    public function includeWidget($name, $controllerName, $viewName = false, $arParams = array()){
        $this->name             = $name;
        $this->params           = $arParams;
        $this->controllerName   = $controllerName;
        $this->viewName         = $viewName;
        $this->path             = CFile::normalizePath("/" . $this->arConfig["path"] . "/" . $this->name . "/");
        
        $this->controllerPath   = $this->path . $this->arConfig["controllerPath"];
        $this->controllerFile   = CFile::normalizePath($this->controllerPath . "/" . $this->controllerName);
        
        if(substr($this->controllerFile, -4) != ".php"){
            $this->controllerFile.= ".php";
        }
        
        if(strpos($this->controllerName, "/") !== false){
            $this->controllerPath = CFile::normalizePath(dirname($this->controllerFile) . "/");
        }
        
        $this->viewPath = $this->path . $this->arConfig["viewPath"];
        $this->viewFile = $this->viewName ? CFile::normalizePath($this->viewPath . "/" . $this->viewName) : false ;
        
        if($this->viewFile){
            if(substr($this->viewFile, -4) != ".php"){
                $this->viewFile.= ".php";
            }
            
            if(strpos($this->viewName, "/") !== false){
                $this->viewPath = CFile::normalizePath(dirname($this->viewFile) . "/");
            }
        }
        
        CEvent::trigger("CORE.WIDGET.CONTROLLER.BEFORE", array($this));

        if(is_file(ROOT_PATH . $this->controllerFile)){
            $obResult       = $this->process(ROOT_PATH . $this->controllerFile);
            $this->content  = $obResult->content;
            $this->result   = $obResult->result;
        }else{
            CEvent::trigger("CORE.WIDGET.CONTROLLER.NOT_FOUND", array($this));
        }
        
        CEvent::trigger("CORE.WIDGET.CONTROLLER.AFTER", array($this));
        
        return $this->result;
    }
    
    protected function getViewProcess($viewName = false, $extractDataKeys = true){
        if($viewName){
            if(substr($viewName, -4) != ".php"){
                $viewName.= ".php";
            }
            
            $this->viewFile = CFile::normalizePath($this->viewPath . "/" . $viewName);
            $this->viewPath = CFile::normalizePath(dirname($this->viewFile) . "/");
        }

        CEvent::trigger("CORE.WIDGET.VIEW.PROCESS.BEFORE", array($this));
        
        if($this->viewFile && is_file(ROOT_PATH . $this->viewFile)){
            $obResult = $this->process(ROOT_PATH . $this->viewFile, $extractDataKeys);
        }else{
            $obResult           = new stdClass;
            $obResult->content  = false;
            $obResult->result   = false;
            CEvent::trigger("CORE.WIDGET.VIEW.NOT_FOUND", array($this));
        }
        
        CEvent::trigger("CORE.WIDGET.VIEW.PROCESS.AFTER", array($this, $obResult));
        return $obResult;
    }
    
    public function getViewContent($viewName = false, $extractDataKeys = true){
        $obResult = $this->getViewProcess($viewName, $extractDataKeys);

        return $obResult->content;
    }
    
    public function getViewResult($viewName = false, $extractDataKeys = true){
        $obResult = $this->getViewProcess($viewName, $extractDataKeys);

        return $obResult->result;
    }
    
    public function includeView($viewName = false, $extractDataKeys = true){
        $obResult = $this->getViewProcess($viewName, $extractDataKeys);
        
        $this->content = $obResult->content;
        
        if($this->content){
            echo $this->content;
        }
        
        return $obResult->result;
    }

    static public function render($name, $controllerName = "index", $viewName = false, $arParams = array(), $arConfig = array()){
        $obWidget = new self($arConfig);
        $obWidget->includeWidget($name, $controllerName, $viewName, $arParams);
        
        if($obWidget->content){
            echo $obWidget->content;
        }
        
        return $obWidget->result;
    }
    
    public function getParams(){
        return $this->params;
    }
    
    public function getParam($paramName){
        return isset($this->params[$paramName]) ? $this->params[$paramName] : NULL ;
    }
}
?>