<?
$widget = $this;

CBreadcrumbs::show(function($items) use($widget){
    return $this->view->render(ROOT_PATH . $widget->viewFile, $items);
});

CBreadcrumbs::add([
    "/" => "Главная",
]);