<?
namespace Helpers;

class CUrl{
    public static function to($url = null, array $params = []){
        $queryParams = "";
        
        if(count($params)){
            $queryParams = urldecode(http_build_query($params, "", "&"));
        }
        
        return ($url ? $url : "") . ($queryParams ? "?" . $queryParams : "");
    }
}