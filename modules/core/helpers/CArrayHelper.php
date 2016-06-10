<?
class CArrayHelper{
    static public function walk(array &$arData, $callback){
        if(is_callable($callback)){
            foreach($arData AS &$value){
                $value = $callback($value);
            }
            
            unset($value);
        }
        
        return true;
    }
    
    static public function map(array $arData, $callback){
        return array_map($callback, $arData);
    }
    
    static public function replace(){
        $arResult = array();
        
        foreach(func_get_args() AS $arData){
            if(!is_array($arData)){
                return NULL;
            }
            
            foreach($arData AS $key => $value){
                $arResult[$key] = $value;
            }
        }
        
        return $arResult;
    }
    
    /**
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = CArrayHelper::getColumn($array, 'id');
     * // the result is: ['123', '345']
     *
     */
    static public function getColumn(array $arData, $key){
        if(strpos($key, ".") === false){
            $arResult = array();
    
    		foreach($arData AS $value){
                if(isset($value[$key])){
                    $arResult[] = $value[$key];
                }
    		}
    
    		$arData = $arResult;
        }else{
            foreach(explode(".", $key) AS $itemKey){
        		$arResult = array();
        
        		foreach($arData AS $value){
                    if(isset($value[$itemKey])){
                        $arResult[] = $value[$itemKey];
                    }
        		}
        
        		$arData = $arResult;
        	}
        }
    	
    	return $arResult;
    }
    
    /**
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = CArrayHelper::index($array, 'id');
     * // the result is:
     * // [
     * //     '123' => ['id' => '123', 'data' => 'abc'],
     * //     '345' => ['id' => '345', 'data' => 'def'],
     * // ]
     */
    static public function index(array $arData, $key, $mergeOnDuplicate = false){
        $arResult   = array();
        $arItem     = reset($arData);
        
        if(is_array($arItem)){
            if(strpos($key, ".") === false){
                if($mergeOnDuplicate){
            		foreach($arData AS $value){
                        if(isset($value[$key])){
                            $arResult[$value[$key]][] = $value;            
                        }
            		}
                }else{
            		foreach($arData AS $value){
                        if(isset($value[$key])){
                            $arResult[$value[$key]] = $value;            
                        }
            		}
                }
            }else{
                if($mergeOnDuplicate){
                    foreach(explode(".", $key) AS $itemKey){
                		$arResult = array();
        
                		foreach($arData AS $value){
                            if(isset($value[$itemKey])){
                                $arResult[$value[$itemKey]][] = $value;            
                            }
                		}
                
                		$arData = $arResult;
                	}
                }else{
                    foreach(explode(".", $key) AS $itemKey){
                		$arResult = array();
        
                		foreach($arData AS $value){
                            if(isset($value[$itemKey])){
                                $arResult[$value[$itemKey]] = $value;            
                            }
                		}
                
                		$arData = $arResult;
                	}
                }
            }
        }
    
    	return $arResult;
    }
    
    static public function isAssoc(array $arData){
        return array_values($arData) !== $arData;
    }
    
    static public function export(array $arData, $tab = "    ", $depth = 0){
        $str = "array(\n";
        
        if(is_array($arData)){
            $nextDepthWS = str_repeat($tab, $depth + 1);
            
            $isAssoc = static::isAssoc($arData);
            
            $count = count($arData);
            
            $i = 0;
            
            foreach($arData AS $key => $value){
                $str.= $nextDepthWS;
                
                if($isAssoc){
                    $str.= is_numeric($key) ? $key : '"' . $key . '"';
                    $str.= " => ";
                }
                
                if(is_array($value)){
                    $str.= static::export($value, $tab, $depth + 1);
                }else{
                    $str.= is_numeric($value) ? $value : '"' . addcslashes($value, "\"") . '"';
                }
                
                if($i !== $count - 1){
                    $str.= ",";
                }
                
                $str.= "\n";
                $i++;
            }
            
            $str.= str_repeat($tab, $depth);
        }
        
        $str.= ")";
        
        if($depth == 0){
            $str.= ';';
        }
        
        return $str;
    }
    
    static public function getKeyValue(array $arData, $key, $value){
        $arResult = array();
        
        if(count($arData)){
            if($key === false || $key == null){ //if key already indexed
                foreach($arData AS $key => $arItem){
                    $arResult[$key] = $arItem[$value];
                }
            }else{
                foreach($arData AS $arItem){
                    $arResult[$arItem[$key]] = $arItem[$value];
                }
            }
        }
        
        return $arResult;
    }
}
?>