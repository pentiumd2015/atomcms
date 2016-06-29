<?
use Helpers\CHtml;
use Helpers\CFile;

class CView extends CObject{
    protected $dynamicPlaceholders = [];
    
    public function render($view, array $data = []){
        $content = "";
        
        if(substr($view, -4) != ".php"){
            $view.= ".php";
        }
        
        if(is_file($view)){
            $content = $this->renderFile($view, $data);
        }
        
        return $content;
    }
    
    protected function renderFile($__file, array $__data = []){
        ob_start();
        ob_implicit_flush(false);
        extract($__data, EXTR_OVERWRITE);
        require($__file);
        return ob_get_clean();
    }
    
    protected function createDynamycPlaceholder($placeholder){
        if(!isset($this->dynamicPlaceholders[$placeholder])){
            $this->dynamicPlaceholders[$placeholder] = [
                "placeholder"   => "<![CDATA[DYNAMIC_PLACEHOLDER_" . count($this->dynamicPlaceholders) . "]]>",
                "data"          => [],
                "callback"      => null
            ];
        }
        
        return $this->dynamicPlaceholders[$placeholder];
    }
    
    public function addDynamic($placeholder, $callback = null, array $data = []){
        $this->addDynamicData($placeholder, $data);
                
        $this->dynamicPlaceholders[$placeholder]["callback"] = $callback;

        return $this->dynamicPlaceholders[$placeholder]["placeholder"];
    }
    
    public function deleteDynamic($placeholder){
        unset($this->dynamicPlaceholders[$placeholder]);
        
        return $this;
    }
    
    public function addDynamicData($placeholder, array $data = []){
        $this->createDynamycPlaceholder($placeholder);
        
        $this->dynamicPlaceholders[$placeholder]["data"] = array_merge_recursive($this->dynamicPlaceholders[$placeholder]["data"], $data);
        
        return $this;
    }
    
    public function setDynamicData($placeholder, array $data = []){
        $this->createDynamycPlaceholder($placeholder);
        
        $this->dynamicPlaceholders[$placeholder]["data"] = $data;
        
        return $this;
    }
    
    public function getDynamicData($placeholder){
        return isset($this->dynamicPlaceholders[$placeholder]) ? $this->dynamicPlaceholders[$placeholder]["data"] : [] ;
    }
    
    public function getDynamicPlaceholder($placeholder){
        $this->createDynamycPlaceholder($placeholder);
        
        return $this->dynamicPlaceholders[$placeholder]["placeholder"];
    }
    public function runDynamic($content){
        CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.BEFORE", [$this]);
        
        $content = $this->dynamicProcess($content);
        
        CEvent::trigger("CORE.TEMPLATE.DYNAMYC_CONTENT.AFTER", [$this]);
        
        return $content;
    }
    
    protected function dynamicProcess($str){
        if(!$str || !count($this->dynamicPlaceholders)){
            return $str;
        }

        $replace = [];
        
        foreach($this->dynamicPlaceholders AS $dynamicPlaceholder){
            if(!is_callable($dynamicPlaceholder["callback"])){
                continue;
            }
            
            $replace[$dynamicPlaceholder["placeholder"]] = call_user_func_array($dynamicPlaceholder["callback"], [$dynamicPlaceholder["data"]]);
        }
        
        return strtr($str, $replace);
    }
    
    protected function addItem($itemID, $str, $priority = false){
        $data = $this->getDynamicData("HTML_HEAD");
        
        if(!isset($data[$itemID])){
            $data[$itemID] = [];
        }
        
        if(!isset($data[$itemID][$str])){
            $data[$itemID][$str] = ($priority !== false) ? (int)$priority : count($data[$itemID]) ;
            
            $this->setDynamicData("HTML_HEAD", $data);
        }
    }
    
    public function addJs($path, $priority = false){
        if(CAtom::$app->request->isAjax()){
            echo $this->getJsString([$path => true]);
            return;
        }
        
        $this->addItem("JS", CFile::normalizePath($path), $priority);
    }
    
    public function addCss($path, $priority = 0){
        if(CAtom::$app->request->isAjax()){
            echo $this->getCssString([$path => true]);
            return;
        }
        
        $this->addItem("CSS", CFile::normalizePath($path), $priority);
    }
    
    public function addString($str, $priority = 0){
        if(CAtom::$app->request->isAjax()){
            echo $this->getStrString([$str => true]);
            return;
        }
        
        $this->addItem("STRING", $str, $priority);
    }
    
    public function showHead(){
        echo $this->addDynamic("HTML_HEAD", [$this, "getHeadContent"]);
    }
    
    protected function getHeadContent(){
        $data = $this->getDynamicData("HTML_HEAD");
        
        $result = "";

        if(isset($data["JS"])){
            $result.= $this->getJsString($data["JS"]);
        }
        
        if(isset($data["CSS"])){
            $result.= $this->getCssString($data["CSS"]);
        }
        
        if(isset($data["STRING"])){
            $result.= $this->getStrString($data["STRING"]);
        }

        return $result;
    }
    
    protected function getJsString(array $data = []){
        uasort($data, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        $str = "";
        
        foreach($data AS $path => $item){
            $str.= "<script type=\"text/javascript\" src=\"" . CHtml::escape($path) . "\"></script>\n";
        }
        
        return $str;
    }
    
    protected function getCssString(array $data = []){
        uasort($data, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        $str = "";
        
        foreach($data AS $path => $item){
            $str.= "<link href=\"" . CHtml::escape($path) . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
        }
        
        return $str;
    }
    
    protected function getStrString(array $data = []){
        uasort($data, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        $str = "";
        
        foreach($data AS $string => $item){
            $str.= CHtml::escape($string). "\n";
        }
        
        return $str;
    }
}