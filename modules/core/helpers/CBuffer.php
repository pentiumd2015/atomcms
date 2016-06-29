<?
namespace Helpers;

class CBuffer{
    public static function start($callback = null){
        ob_start($callback);
    }
    
    public static function end(){
        return ob_get_clean();
    }
    
    public static function clear(){
        while(ob_get_level()){
            ob_end_clean(); 
        }
    }
}