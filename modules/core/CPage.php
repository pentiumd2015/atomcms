<?
class CPage{
    const DYNAMIC_KEY_NAME = "TEMPLATE_HEAD";
    
    public function __construct(){
        $this->dynamic = CAtom::$app->template->dynamic;
    }
    
    public function addJS($path, $priority = false){
        $arData = $this->dynamic->getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["JS"]) || !isset($arData["JS"][$path])){
            $count = isset($arData["JS"]) ? count($arData["JS"]) : 0 ;
            $priority = $priority !== false ? (int)$priority : $count;
            
            $this->dynamic->addData(self::DYNAMIC_KEY_NAME, array(
                "JS" => array($path => $priority)
            ));
        }
    }
    
    public function addCSS($path, $priority = 0){
        $arData = $this->dynamic->getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["CSS"]) || !isset($arData["CSS"][$path])){
            $count = isset($arData["CSS"]) ? count($arData["CSS"]) : 0 ;
            $priority = $priority !== false ? (int)$priority : $count;
            
            $this->dynamic->addData(self::DYNAMIC_KEY_NAME, array(
                "CSS" => array($path => $priority)
            ));
        }
    }
    
    public function addString($str, $priority = 0){
        $arData = $this->dynamic->getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["STRING"]) || !isset($arData["STRING"][$str])){
            $count = isset($arData["STRING"]) ? count($arData["STRING"]) : 0 ;
            $priority = $priority !== false ? (int)$priority : $count;
            
            $this->dynamic->addData(self::DYNAMIC_KEY_NAME, array(
                "STRING" => array($str => $priority)
            ));
        }
    }
    
    public function showHead(){
        echo $this->dynamic->add(self::DYNAMIC_KEY_NAME, array(__CLASS__, "_showHead"));
    }
    
    public function _showHead($arData){            
        $result = NULL;
        
        if(isset($arData["JS"]) && is_array($arData["JS"])){
            $result.= self::_getJsString($arData["JS"]);
        }
        
        if(isset($arData["CSS"]) && is_array($arData["CSS"])){
            $result.= self::_getCssString($arData["CSS"]);
        }
        
        if(isset($arData["STRING"]) && is_array($arData["STRING"])){
            $result.= self::_getStrString($arData["STRING"]);
        }

        return $result;
    }
    
    protected function _getJsString($arData = array()){
        $str = "";

        uasort($arData, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        foreach($arData AS $path => $arItem){
            $str.= "<script type=\"text/javascript\" src=\"" . CHtml::chars($path) . "\"></script>\n";
        }
        
        return $str;
    }
    
    protected function _getCssString($arData = array()){
        $str = "";
        
        uasort($arData, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        foreach($arData AS $path => $arItem){
            $str.= "<link href=\"" . CHtml::chars($path) . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
        }
        
        return $str;
    }
    
    protected function _getStrString($arData = array()){
        $str = "";
        
        uasort($arData, function($a, $b){
            if($a == $b){
                return 0;
            }
            
            return $a > $b ? 1 : -1 ;
        });
        
        foreach($arData AS $string => $arItem){
            $str.= CHtml::chars($string). "\n";
        }
        
        return $str;
    }
}