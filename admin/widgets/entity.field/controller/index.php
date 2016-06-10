<?
use \Helpers\CHttpResponse;

$entityID   = $this->getParam("entityID");
$listURL    = $this->getParam("listURL");
$addURL     = $this->getParam("addURL");
$editURL    = $this->getParam("editURL");

$obRoute    = $this->app("route");
$fileName   = false;

switch($obRoute->path){
    case $listURL:
        $fileName = "list.php";
        break;
    case $addURL:
        $fileName = "add.php";
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
    $addURL     = str_replace("{ID}", $entityID, $this->getParam("addURL"));
    
    \Page\Breadcrumbs::add(array(
        $entityURL  => "Редактирование сущности",
        $listURL    => "Список полей"
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