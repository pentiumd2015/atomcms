<?
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

if($fileName && is_file($filePath . $fileName)){
    include($filePath . $fileName);
}else{
    CEvent::trigger("404");
}
?>