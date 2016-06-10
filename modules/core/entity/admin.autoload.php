<?
CEvent::on("CORE.ROUTER.BEFORE", function($obApp, $obRouter){
    $arRoutes = CFile::getArrayFromFile(__DIR__ . "/routes.php");
    $obRouter->addRoutes($arRoutes);
});
?>