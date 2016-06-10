<?
class CBlock extends CView{
    public $path;
    public $file;
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
    
    public function __construct(array $arConfig = array()){
        $this->arConfig = $arConfig;
    }
    
    public function includeBlock($name, $arParams = array()){
        $this->name    = $name;
        $this->params  = $arParams;
        $this->path    = CFile::normalizePath("/" . $this->arConfig["path"]);
        $this->file    = CFile::normalizePath($this->path . "/" . $name);
        
        if(substr($this->file, -4) != ".php"){
            $this->file.= ".php";
        }
        
        CEvent::trigger("CORE.BLOCK.FILE.BEFORE", array($this));

        if(is_file(ROOT_PATH . $this->file)){
            $obResult       = $this->process(ROOT_PATH . $this->file);
            $this->content  = $obResult->content;
            $this->result   = $obResult->result;
        }else{
            CEvent::trigger("CORE.BLOCK.NOT_FOUND", array($this));
        }
        
        CEvent::trigger("CORE.BLOCK.FILE.AFTER", array($this));
        
        return $this->result;
    }
    
    static public function render($name, $arParams = array(), $arConfig = array()){
        $arConfig+= self::getConfig();
        
        $obBlock = new CBlock($arConfig);
        $obBlock->includeBlock($name, $arParams);
        
        if($obBlock->content){
            echo $obBlock->content;
        }
        
        return $obBlock->result;
    }
}
?>