<?
class CStorage{       
    private static $arStorage = array();
    
    public static function set($mixed = NULL, $value = NULL){
        if(is_array($mixed)){                
            return self::$arStorage = array_merge(self::$arStorage, $mixed);
        }else{
            return self::$arStorage[$mixed] = $value;
        }
    }
    
    public static function add($mixed = NULL, $value = NULL){
        if(is_array($mixed)){                
            return self::$arStorage = array_merge_recursive(self::$arStorage, $mixed);
        }
        
        return self::$arStorage[$mixed][] = $value;
    }

    public static function get($key){
        return isset(self::$arStorage[$key]) ? self::$arStorage[$key] : false ;
    }
    
    public static function getAll(){            
        return self::$arStorage;
    }
}
?>