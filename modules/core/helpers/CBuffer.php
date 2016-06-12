<?
namespace Helpers;

class CBuffer{
    static public function start($callback = NULL){
        ob_start($callback);
    }
    
    static public function end(){
        return ob_get_clean();
    }
    
    static public function clear(){
        while(ob_get_level()){
            ob_end_clean(); 
        }
    }
}
?>