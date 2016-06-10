<?
class CEvent{
    private static $arEvents = array();
    
    static public function on($eventName, $callback = NULL){
        if(is_callable($callback)){
            self::$arEvents[$eventName][] = $callback;
        }else if(is_array($eventName)){
            foreach($eventName AS $event => $callback){
                if(is_callable($callback)){
                    self::$arEvents[$event][] = $callback;
                }
            }
        }else if(is_array($callback)){
            if(!isset(self::$arEvents[$eventName])){
                self::$arEvents[$eventName] = array();
            }
            
            self::$arEvents[$eventName] = array_merge_recursive(self::$arEvents[$eventName], $callback);
        }
    }
    
    static public function trigger($eventName, $arParams = array()){
        if(isset(self::$arEvents[$eventName])){
            $arResult = array();
            
            foreach(self::$arEvents[$eventName] AS $callback){
                $arResult[$eventName][] = call_user_func_array($callback, $arParams);
            }
            
            return $arResult;
        }else{
            return false;
        }
    }
}
?>