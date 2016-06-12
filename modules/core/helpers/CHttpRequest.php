<?
namespace Helpers;

class CHttpRequest{
    public function isAjax(){
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }

    public function request($name = null, $defaultValue = null){
        return $this->getQueryParams($_REQUEST, $name, $defaultValue);
    }
    
    public function get($name = null, $defaultValue = null){
        return $this->getQueryParams($_GET, $name, $defaultValue);
    }
    
    public function post($name = null, $defaultValue = null){
        return $this->getQueryParams($_POST, $name, $defaultValue);
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
    
    protected function getQueryParams(array $array = [], $name = null, $defaultValue = null){
        if($name == null){
            return $array;
        }else{
            return isset($array[$name]) ? $array[$name] : $defaultValue;
        }
    }
}