<?
class CRoute{
    public $active = true;
    public $templates = array();
    public $path;
    public $url;
    protected $varValues = [];
    
    public function __construct(array $arRoute){
        foreach($arRoute AS $property => $value){
            $this->{$property} = $value;
        }
    }
    
    public function getVarValues(){
        return isset($this->varValues) ? $this->varValues : false ;
    }
    
    public function getVarValue($paramName){
        $arValues = $this->getVarValues();

        return isset($arValues[$paramName]) ? $arValues[$paramName] : false ;
    }
    
    static public function getMatch($uri, $pattern, $arPatternParams = array()){
        $arResult = [];
        
        if($uri == $pattern && strpos($uri, "{") === false){
            return $arResult;
        }
        
        $regexp = "#^". preg_replace("#\{.+?\}#", "(.+?)", $pattern) ."$#";

		$arValues = array();
        
		if (!preg_match($regexp, $uri, $arValues)){
            return false;
        }
        
        $arMatches = array();
              
        if(preg_match_all("#\{(.+?)\}#", $pattern, $arMatches)){
            if(count($arPatternParams)){
                foreach($arMatches[1] AS $key => $match){
                    if(isset($arPatternParams[$match])){
                        $arParamMatch = array();
                        
                        if(preg_match("#" . $arPatternParams[$match] . "#", $arValues[$key + 1], $arParamMatch)){
                            $countMatch = count($arParamMatch);
                            
                            if($countMatch == 1 || $countMatch == 2){
                                $arResult[$match] = $arParamMatch[$countMatch - 1];
                            }else{
                                array_shift($arParamMatch);
                                $arResult[$match] = $arParamMatch;
                            }
                        }else{
                            return false;
                        }
                    }else{
                        $arResult[$match] = $arValues[$key + 1];
                    }
                }
            }else{
                //array_shift($arValues);
        	    //$arResult = array_combine($arMatches[1], $arValues); 
            
                foreach($arMatches[1] AS $key => $match){
                    $arResult[$match] = $arValues[$key + 1];
                }
            }
            
            return $arResult;
        }

		return false;
	}
}
?>