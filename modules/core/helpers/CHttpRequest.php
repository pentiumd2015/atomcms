<?
namespace Helpers;

class CHttpRequest{
    public function isAjax(){
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }

    public function request($name = null, $nullValue = null){
        return $this->getQueryParams($_REQUEST, $name, $nullValue);
    }
    
    public function get($name = null, $nullValue = null){
        return $this->getQueryParams($_GET, $name, $nullValue);
    }
    
    public function post($name = null, $nullValue = null){
        return $this->getQueryParams($_POST, $name, $nullValue);
    }
    
    public function isPost(){
        return $_SERVER["REQUEST_METHOD"] == "POST";
    }
    
    public function isGet(){
        return $_SERVER["REQUEST_METHOD"] == "GET";
    }
    
    public function isRequest(){
        return ($this->isGet() || $this->isPost());
    }
    
    protected function getQueryParams(array $array = [], $name = null, $nullValue = null){
        if($name === null){
            return $array;
        }else{
            return isset($array[$name]) ? $array[$name] : $nullValue ;
        }
    }
}