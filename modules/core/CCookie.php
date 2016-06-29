<?

class CCookie{
    const EXPIRE    = "expire";
    const DOMAIN    = "domain";
    const PATH      = "path";
    const SECURE    = "secure";
    const HTTP_ONLY = "httpOnly";
    
    static public function set($name, $value = "", $expireTime = 0, $arParams = array()){
        $path       = "/";
        $domain     = "";
        $isSecure   = false;
        $isHttpOnly = false;
        
        if(is_array($arParams)){
            if(isset($arParams[self::EXPIRE])){
                $expireTime = $arParams[self::EXPIRE];
            }
            
            if(isset($arParams[self::PATH])){
                $path = $arParams[self::PATH];
            }
            
            if(isset($arParams[self::DOMAIN])){
                $domain = $arParams[self::DOMAIN];
            }
            
            if(isset($arParams[self::SECURE])){
                $isSecure = $arParams[self::SECURE];
            }
            
            if(isset($arParams[self::HTTP_ONLY])){
                $isHttpOnly = $arParams[self::HTTP_ONLY];
            }
        }
        
        return setcookie($name, $value, $expireTime, $path, $domain, $isSecure, $isHttpOnly);
    }
    
    static public function remove($name){
        return self::set($name, "", time() - 1);
    }
    
    static public function get($name){
        return $_COOKIE && isset($_COOKIE[$name]) ? $_COOKIE[$name] : false ;
    }
    
    static public function getAll(){
        return $_COOKIE ? $_COOKIE : false ;
    }
}