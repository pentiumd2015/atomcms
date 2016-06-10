<?
class CConstruct{
    static protected $_arInstances;
    
    protected $_arProperties    = array();
    protected $_arMethods       = array();
    
    
    static public function getInstance(){
        $class = get_called_class();

        if(!isset(static::$_arInstances[$class])){
            static::$_arInstances[$class] = new static;
        }
        
        return static::$_arInstances[$class];
    }
    
    public function __set($name, $value){
        return $this->_arProperties[$name] = $value;
    }
    
    public function __get($name){
        return isset($this->_arProperties[$name]) ? $this->_arProperties[$name] : false ;
    }
    
    static public function __callStatic($name, $args){
        $preffix    = substr($name, 0, 3);
        $prop       = substr($name, 3);
        $prop[0]    = strtolower($prop[0]);
        
        $class = get_called_class();
        
        if(property_exists($class, $prop)){
            if($preffix == "get"){
                return $class::${$prop};
            }else if($preffix == "set"){
                return $class::${$prop} = $args[0];
            }
        }else{
            throw new \CException("Property " . $name . " of class " . $class . " not found");
        }
    }
    
    public function __call($name, $args){
        if(isset($this->_arMethods[$name])){
            //$obFunction = new ReflectionFunction($this->_arMethods[$name]);
            //return $obFunction->invokeArgs($args);
            if(is_callable($this->_arMethods[$name])){
                return call_user_func_array($this->_arMethods[$name], $args);
            }else{
                throw new \CException("Method " . $name . " of class " . get_called_class() . " not found");
            }
        }else{
            $preffix        = substr($name, 0, 3);
            $property       = substr($name, 3);
            $property[0]    = strtolower($property[0]);

            if($preffix == "get"){
                return $this->{$property};
            }else if($preffix == "set"){
                return $this->{$property} = $args[0];
            }else{
                throw new \CException("Property " . $name . " of class " . get_called_class() . " not found");
            }
        }
    }
    
    public function setMethod($method, $function = NULL){
        if(is_callable($function)){
            $this->_arMethods[$method] = $function;
        }
        
        return $this;
    }
    
    public function deleteMethod($method){
        unset($this->_arMethods[$method]);
        
        return $this;
    }
    
    public function app($property = false){
        $result = \CStorage::get("app");
        
        if(!is_object($result)){
            return false;
        }
        
        if($property){
            $result = $result->{$property};
        }
        
        return $result;
    }
}
?>