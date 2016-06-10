<?
use \Entities\Entity;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$listEntityURL  = $this->getParam("listEntityURL");
$listElementURL = $this->getParam("listElementURL");
$listSectionURL = $this->getParam("listSectionURL");
$addElementURL  = $this->getParam("addElementURL");
$addSectionURL  = $this->getParam("addSectionURL");
$editElementURL = $this->getParam("editElementURL");
$editSectionURL = $this->getParam("editSectionURL");

$obRoute    = $this->app("route");
$fileName   = false;

switch($obRoute->path){
    case $listEntityURL:
        $fileName = "listEntity.php";
        break;
    case $listElementURL:
        $fileName = "listElement.php";
        break;
    case $listSectionURL:
        $fileName = "listSection.php";
        break;
    case $addElementURL:
        $fileName = "addElement.php";
        break;
    case $addSectionURL:
        $fileName = "addSection.php";
        break;
    case $editElementURL:
        $fileName = "editElement.php";
        break;
    case $editSectionURL:
        $fileName = "editSection.php";
        break;
    default:
        $fileName = false;
        break;
}

$filePath = __DIR__ . "/include/";

if($fileName && is_file($filePath . $fileName)){
    $entityID = (int)$obRoute->getVarvalue("ENTITY_ID");
    
    if($entityID){
        $obEntity           = Entity::findByPk($entityID);
        $obEntity->params   = CJSON::decode($obEntity->params, true);
        
        if(!$obEntity){
            CHttpResponse::redirect("/404/");
        }
        
        $addElementURL      = str_replace("{ENTITY_ID}", $obEntity->entity_id, $addElementURL);
        $listElementURL     = str_replace("{ENTITY_ID}", $obEntity->entity_id, $listElementURL);
        $listSectionURL     = str_replace("{ENTITY_ID}", $obEntity->entity_id, $listSectionURL);
        $addSectionURL      = str_replace("{ENTITY_ID}", $obEntity->entity_id, $addSectionURL);
        $editElementURL     = str_replace("{ENTITY_ID}", $obEntity->entity_id, $editElementURL);
        $editSectionURL     = str_replace("{ENTITY_ID}", $obEntity->entity_id, $editSectionURL);
    }
    
    include($filePath . $fileName);
}else{
    echo "404";exit;
}
?>

<?php 

?>

<?php 

?>