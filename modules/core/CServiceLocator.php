<?

class CServiceLocator extends CObject{
    private $services   = [];
    private $instances  = [];
    
    public function __get($name){
        return $this->has($name) ? $this->get($name) : parent::__get($name) ;
    }
    
    public function __isset($name){
        return $this->has($name) ? true : parent::__isset($name) ;
    }

    public function has($service){
        return isset($this->instances[$service]) || isset($this->services[$service]);
    }

    public function get($service){
        if(isset($this->instances[$service])){
            return $this->instances[$service];
        }
        
        if(isset($this->services[$service])){ 
            list($object, $params) = is_array($this->services[$service]) ? $this->services[$service] : [$this->services[$service], []] ;

            if(is_object($object)){
                return $this->instances[$service] = ($object instanceof Closure) ? $object($params) : $object ;
            }else if(class_exists($object, true)){
                $reflection = new ReflectionClass($object);
                
                if($reflection->getConstructor() !== null){
                    return $this->instances[$service] = $reflection->newInstanceArgs($params);
                }else{
                    return $this->instances[$service] = new $object;
                }
            }
        }
        
        return null;
    }
    
    public function set($service, $object = null, array $params = []){
        $services = is_array($service) ? $service : [$service => [$object, $params]] ;

        foreach($services AS $service => $options){
            list($object, $params) = is_array($options) ? $options : [$options, []] ;
            
            if($object === null){
                unset($this->instances[$service], $this->services[$service]);
                continue;
            }
    
            unset($this->instances[$service]);
            
            $this->services[$service] = $options;
        }
    }
    
    public function clear($service){
        unset($this->services[$service], $this->instances[$service]);
    }
}