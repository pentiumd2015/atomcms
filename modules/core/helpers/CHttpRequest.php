<?
class CHttpRequest{
    static public function toQuery($arParams = array(), $isOnlyParams = false, $separator = "&"){            
        $result = "";
        
        if(!$isOnlyParams){
            $arParams = array_merge($_GET, $arParams);
        }
        
        /*
        p(urldecode(http_build_query($arParams, "", $separator)));
        foreach($arParams AS $key => $val){
            $result.= $separator . $key . $equalSymbol . $val ;
        }
        
        if($result){
            $result = ltrim($result, $separator);
        }
        */
        return urldecode(http_build_query($arParams, "", $separator));
    }
    
    static public function isAjax(){
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }
    
    static public function get($key = NULL){
        if(!$key){
            return $_REQUEST;
        }else{
            return isset($_REQUEST[$key]) ? $_REQUEST[$key] : NULL ;
        }
    }
    
    static public function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    static public function isGet(){
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    
    static public function isRequest(){
        return (self::isPost() || self::isGet());
    }
}
?>