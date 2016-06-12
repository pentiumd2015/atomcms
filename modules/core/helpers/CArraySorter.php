<?
namespace Helpers;

class CArraySorter{
    static public function sort(array $arData, $key, $direction = SORT_ASC){
        if(is_callable($key)){
            $callback = $key;
        }else{
            if($direction == SORT_ASC){
                $callback = function($a, $b) use ($key){
                    if($a[$key] == $b[$key]){
                        return 0;
                    }
                    
                    return $a[$key] > $b[$key] ? 1 : -1 ;
                };
            }else{
                $callback = function($a, $b) use ($key){
                    if($a[$key] == $b[$key]){
                        return 0;
                    }
                    
                    return $a[$key] < $b[$key] ? 1 : -1 ;
                };
            }
        }
        
        uasort($arData, $callback);
        
        return $arData;
    }
    /*
    static public function natsort($arData, $direction = SORT_ASC, $caseSensitive = false){
        if($caseSensitive){
            natsort($arData);
        }else{
            natcasesort($arData);
        }
        
        return ($direction == SORT_ASC) ? $arData : array_reverse($arData);
    }
    */
    /*
    $ar = array(
        array("id" => 2, "p" => 35),
        array("id" => 1, "p" => 21),
        array("id" => 7, "p" => 67),
    );
    
    $arr = CSorter::multiSort($ar, array("id" => SORT_ASC, "p" => SORT_ASC,));
    */        
    static public function multiSort(array $arData, $arColumns = array()){
        $i = 0;
        
        foreach($arColumns AS $column => $direction){
            foreach($arData AS $key => $arItem){
                $arArgs[$i][$key] = $arItem[$column];
            }
            
            $i++;
            
            $arArgs[$i] = $direction;
            
            $i++;
        }
        
        $arArgs[] = &$arData;
        
        call_user_func_array("array_multisort", $arArgs);
        
        return end($arArgs);
    }
}