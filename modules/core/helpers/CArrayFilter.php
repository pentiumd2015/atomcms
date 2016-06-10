<?
class CArrayFilter{
    static public function filter(array $arData, $callback = NULL){
        if(is_callable($callback)){
            return array_filter($arData, $callback);
        }
        
        return array_filter($arData);
    }
}
?>