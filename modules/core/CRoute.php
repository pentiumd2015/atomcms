<?

class CRoute{
    public $active      = true;
    public $layoutFile  = null;
    public $pageFile    = null;
    public $query       = null;
    public $path;
    public $url;
    
    public function __construct(array $route = []){
        foreach($route AS $property => $value){
            $this->{$property} = $value;
        }
    }
    
    public static function getMatchParams($uri, $pattern, array $params = []){
        $result = [];
        
        if($uri == $pattern && strpos($uri, "{") === false){
            return $result;
        }

		$values = [];

		if(!preg_match("#^". preg_replace("#\{.+?\}#", "(.+?)", $pattern) ."$#", $uri, $values)){
            return false;
        }
        
        $matches = [];
              
        if(preg_match_all("#\{(.+?)\}#", $pattern, $matches, PREG_SET_ORDER)){
            if(count($params)){
                foreach($matches AS $key => $match){
                    $value = $match[1];
                    if(isset($params[$value])){
                        $paramMatch = [];
                        
                        if(!preg_match("#" . $params[$value] . "#", $values[$key + 1], $paramMatch)){
                            return false;
                        }
                        
                        if(count($paramMatch) == 1){
                            $result[$value] = $paramMatch[0];
                        }else{
                            array_shift($paramMatch);
                            $result[$value] = $paramMatch;
                        }
                    }else{
                        $result[$value] = $values[$key + 1];
                    }
                }
            }else{
                foreach($matches AS $key => $match){
                    $result[$match[1]] = $values[$key + 1];
                }
            }
            
            return $result;
        }

		return false;
	}
    
    public function getMatch(array $routes = []){
        $query = CAtom::$app->route->query;
        
        if(!$query){
            return false;
        }
        
        foreach($routes AS $mode => $params){
            $params["varParams"] = isset($params["varParams"]) ? $params["varParams"] : [] ; 
            
            if(($varValues = $this->getMatchParams($query, $params["pattern"], $params["varParams"])) !== false){
                $params["mode"]         = $mode;
                $params["varValues"]    = $varValues;
                
                return $params;
            }
        }
        
        return false;
	}
}