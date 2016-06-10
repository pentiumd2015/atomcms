<?
$obWidget = $this;

CBreadcrumbs::show(function($arItems) use ($obWidget){
    $arItems = array("/" => "Главная") + $arItems;

    if(count($arItems) > 1){
        $obWidget->setData(array(
            "arItems" => $arItems
        ));
        
        return $obWidget->getViewContent();
    }
    
    return "";
});
?>

<?php 

?>

<?php 

?>