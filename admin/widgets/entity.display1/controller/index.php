<?
use \Helpers\CHttpResponse;

$entityID   = $this->getParam("entityID");
$listURL    = $this->getParam("listURL");
$editURL    = $this->getParam("editURL");

$obRoute    = $this->app("route");
$fileName   = false;

switch($obRoute->path){
    case $listURL:
        $fileName = "list.php";
        break;
    case $editURL:
        $fileName = "edit.php";
        break;
    default:
        $fileName = false;
        break;
}

$filePath = __DIR__ . "/include/";

if($fileName && is_file($filePath . $fileName) && ($obEntity = \Entities\Entity::findByPk($entityID))){
    $entityURL  = str_replace("{ID}", $entityID, $this->getParam("entityURL"));
    $listURL    = str_replace("{ID}", $entityID, $this->getParam("listURL"));
    $editURL    = str_replace("{ID}", $entityID, $this->getParam("editURL"));
    
    \Page\Breadcrumbs::add(array(
        $entityURL  => "Редактирование сущности",
        $listURL    => "Настройка отображения"
    ));

    include($filePath . $fileName);
}else{
    echo "404";exit;
}
?>

<?php 

?>

<?php 

?>