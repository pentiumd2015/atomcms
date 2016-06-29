<?
$request = CAtom::$app->request;

if($request->isAjax() && ($widgetName = $request->request("widget"))){
    CWidget::render($widgetName, "ajax");
}