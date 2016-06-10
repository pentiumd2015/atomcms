<?
class CHttpResponse{
    const JSON = "application/json";
	const HTML = "text/html";
	const XML  = "text/xml";
	const TEXT = "text/plain";
	const GIF  = "image/gif";
	const JPEG = "image/jpeg";
    const UTF8 = "UTF-8";
    const JS   = "text/javascript";
    
    protected static $arMessages = array(
        // Informational 1xx
        100 => "Continue",
        101 => "Switching Protocols",
    
        // Success 2xx
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
    
        // Redirection 3xx
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",  // 1.1
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        // 306 is deprecated but reserved
        307 => "Temporary Redirect",
    
        // Client Error 4xx
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
    
        // Server Error 5xx
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        509 => "Bandwidth Limit Exceeded"
    );
    
    static public function setHeader($header, $isReplace = true, $code = NULL){
        //if(!headers_sent()){
            header($header, $isReplace, $code);
       // }
    }
    
    static public function setHeaders($arHeaders = array()){
        if(/*!headers_sent() && */is_array($arHeaders)){
            foreach($arHeaders AS $key => $header){
                header($key . ": " . $header);
            }
            
            return true;
        }else{
            return false;
        }
    }
    
    static public function setCode($code){
        self::setHeader(self::$arMessages[$code], true, $code);
    }
    
    static public function setType($type){
        self::setHeader("Content-Type: " . $type);
	}
    
    static public function redirect($location, $code = 302){
        self::setHeader("Location: " . $location, true, $code);
    }
    /*
    static public function toJSON($data, $flag = JSON_UNESCAPED_UNICODE, $depth = 512){
        return json_encode($data, $flag, $depth);
    }*/
}
?>