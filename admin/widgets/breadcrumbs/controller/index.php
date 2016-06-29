<?
$widget = $this;

CBreadcrumbs::show(function($data) use($widget){
    if(!is_array($data["items"])){
        $data["items"] = [];
    }
    
    $data["items"] = array_merge(["/" => "Главная"], $data["items"]);
    $widget->setViewData($data);
    
    return $widget->includeView($widget->viewName, true);
});