<?
class CDynamicContent{
    static $arDynamic = array();
    
    static protected function _createDynamyc($alias = NULL){
        if(!isset(self::$arDynamic[$alias])){
            self::$arDynamic[$alias] = array(
                "MARKER"    => "[#DYNAMIC_CONTENT_" . count(self::$arDynamic) . "#]",
                "CALLBACK"  => NULL,
                "DATA"      => array()
            );
        }
        
        return self::$arDynamic[$alias];
    }
    
    static public function add($alias = NULL, $callback = NULL){
        if(!$alias){
            return false;
        }
        
        self::_createDynamyc($alias);
        
        if($callback){
            self::$arDynamic[$alias]["CALLBACK"] = $callback;
        }

        return self::$arDynamic[$alias]["MARKER"];
    }
    
    static public function delete($alias = NULL){
        unset(self::$arDynamic[$alias]);
    }
    
    static public function setData($alias = NULL, $arData = array()){
        self::_createDynamyc($alias);
        
        if(is_array($arData)){
            self::$arDynamic[$alias]["DATA"] = $arData;
        }
        
        return self::$arDynamic[$alias]["DATA"];
    }
    
    static public function getData($alias){
        return isset(self::$arDynamic[$alias]) ? self::$arDynamic[$alias]["DATA"] : array() ;
    }
    
    static public function addData($alias = NULL, $arData = array()){
        self::_createDynamyc($alias);
        
        if(is_array($arData)){
            self::$arDynamic[$alias]["DATA"] = array_merge_recursive(self::$arDynamic[$alias]["DATA"], $arData);
        }
        
        return self::$arDynamic[$alias]["DATA"];
    }
    
    static public function getMarker($alias){
        if(!isset(self::$arDynamic[$alias])){
            self::_createDynamyc($alias);
        }
        
        return self::$arDynamic[$alias]["MARKER"];
    }
    
    static public function process($str = NULL){
        return self::_process($str);
    }
    
    protected static function _process($str = NULL){
        if(!$str || !count(self::$arDynamic)){
            return $str;
        }
        
        $arDynamic = self::$arDynamic;
        
        self::$arDynamic = array();
        
        $arReplace = array();
        
        foreach($arDynamic AS $arDynamicItem){
            if(!is_callable($arDynamicItem["CALLBACK"])){
                continue;
            }
            
            $arReplace[$arDynamicItem["MARKER"]] = call_user_func_array($arDynamicItem["CALLBACK"], array($arDynamicItem["DATA"]));
        }

        unset($arDynamic);
        
        return self::_process(strtr($str, $arReplace));
    }
}
?>