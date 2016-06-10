<?
class CPage{
    const DYNAMIC_KEY_NAME = "TEMPLATE_HEAD";
    
    static public function addJS($path, $priority = false){
        $arData = CDynamicContent::getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["JS"]) || !isset($arData["JS"][$path])){
            $priority = $priority !== false ? (int)$priority : count($arData["JS"]);
            
            CDynamicContent::addData(self::DYNAMIC_KEY_NAME, array(
                "JS" => array($path => $priority)
            ));
        }
    }
    
    static public function addCSS($path, $priority = 0){
        $arData = CDynamicContent::getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["CSS"]) || !isset($arData["CSS"][$path])){
            $priority = $priority !== false ? (int)$priority : count($arData["CSS"]);
            
            CDynamicContent::addData(self::DYNAMIC_KEY_NAME, array(
                "CSS" => array($path => $priority)
            ));
        }
    }
    
    static public function addString($str, $priority = 0){
        $arData = CDynamicContent::getData(self::DYNAMIC_KEY_NAME);
        
        if(!isset($arData["STRING"]) || !isset($arData["STRING"][$str])){
            $priority = $priority !== false ? (int)$priority : count($arData["STRING"]);
            
            CDynamicContent::addData(self::DYNAMIC_KEY_NAME, array(
                "STRING" => array($str => $priority)
            ));
        }
    }
    
    static public function showHead(){
        echo CDynamicContent::add(self::DYNAMIC_KEY_NAME, array(__CLASS__, "_showHead"));
    }
    
    static public function _showHead($arData){            
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
    
    static protected function _getJsString($arData = array()){
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
    
    static protected function _getCssString($arData = array()){
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
    
    static protected function _getStrString($arData = array()){
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
?>