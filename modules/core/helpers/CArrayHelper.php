<?
namespace Helpers;

class CArrayHelper{
    public static function replace(){
        $result = [];
        
        foreach(func_get_args() AS $data){
            if(!is_array($data)){
                return null;
            }
            
            foreach($data AS $key => $value){
                $result[$key] = $value;
            }
        }
        
        return $result;
    }

    public static function getColumn(array $data, $key){
        $result = [];

        foreach($data AS $value){
            $result[] = $value[$key];
        }
    	
    	return $result;
    }

    public static function index(array $data, $key, $mergeOnDuplicate = false){
        $result = [];

        if($mergeOnDuplicate){
            foreach($data AS $value){
                $result[$value[$key]][] = $value;
            }
        }else{
            foreach($data AS $value){
                $result[$value[$key]] = $value;
            }
        }
    
    	return $result;
    }
    
    public static function isAssoc(array $data){
        return array_values($data) !== $data;
    }
    
    public static function export(array $data, $tab = "    ", $depth = 0){
        $str = "array(\n";
        
        if(is_array($data)){
            $nextDepthWS = str_repeat($tab, $depth + 1);
            
            $isAssoc = static::isAssoc($data);
            
            $count = count($data);
            
            $i = 0;
            
            foreach($data AS $key => $value){
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
    
    public static function map(array $data, $key = null, $value){
        $result = [];

        if($key === null){ //if key already indexed
            foreach($data AS $key => $item){
                $result[$key] = $item[$value];
            }
        }else{
            foreach($data AS $item){
                $result[$item[$key]] = $item[$value];
            }
        }
        
        return $result;
    }
}