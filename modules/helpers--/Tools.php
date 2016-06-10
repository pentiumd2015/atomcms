<?
namespace Helpers;

class Tools{
    public static function p(){
        foreach(func_get_args() AS $arg){
            echo '<pre>' . print_r($arg, true) . '</pre>';
        }
    }
}
?>