<?
if(CHttpRequest::isAjax()){
    $widgetName = $_REQUEST["widget"];
    
    if($widgetName){
        $arConfig   = CWidget::getConfig();
        $widgetPath = CFile::normalizePath(ROOT_PATH . "/" . $arConfig["path"] . "/" . $widgetName);
        
        if(is_dir($widgetPath)){
            CWidget::render($widgetName, "ajax");
        }else{
            throw new CException("Widget [" . $widgetName . "] not found");
        }
    }
}
?>