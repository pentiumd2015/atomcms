<?
class CBreadcrumbs{
    const DEFERRED_EVENT_KEY = "BREADCRUMBS";
    
    static public function add($arData){
        CDynamicContent::addData(static::DEFERRED_EVENT_KEY, $arData);
    }
    
    static public function show($callback){
        if(is_callable($callback)){
            echo CDynamicContent::add(static::DEFERRED_EVENT_KEY, $callback);
        }
    }
}
?>