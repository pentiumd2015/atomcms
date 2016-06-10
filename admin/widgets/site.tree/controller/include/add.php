<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CHttpRequest;
use \Helpers\CJSON;
use \Helpers\CFile;

$arFormData = isset($_REQUEST["route"]) ? $_REQUEST["route"] : array();

$arErrors = array();

if($_REQUEST["route"]){
    $arData = $_REQUEST["route"];
    
    if(!is_array($arData["templates"])){
        $arData["templates"] = array();
    }
    
    $priority = 0;
    
    foreach($arData["templates"] AS &$arTemplateItem){
        $arTemplateItem["priority"] = $priority;
        $priority++;
    }
    
    unset($arTemplateItem);
    
    $arSaveResult = CRouter::add($arData["site_id"], $arData);

    if(CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = $editURL . "?" . CHttpRequest::toQuery(array(
                "site_id"   => urlencode($arData["site_id"]), 
                "path"      => urlencode($arSaveResult["id"])
            ));
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $redirectURL  = $editURL . "?" . CHttpRequest::toQuery(array(
                "site_id"   => urlencode($arData["site_id"]), 
                "path"      => urlencode($arSaveResult["id"])
            ));
            
            CHttpResponse::redirect($redirectURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
   }
}

$arTemplates = CTemplate::getList();

$arTemplatesOptionList = CArrayHelper::getKeyValue($arTemplates, "template_id", "title");


$obApp = \Application\CApplication::getInstance();
$arConfig = $obApp->getConfig();

$arTemplatePages    = array();
$arTemplateLayouts  = array();

foreach($arTemplates AS $arTemplate){
    $arTemplatePages[$arTemplate["template_id"]] = array("" => "Не выбрана");
    
    $pagePath = CFile::normalizePath(ROOT_PATH . $arConfig["template"]["path"] . "/" . $arTemplate["path"] . "/" . $arConfig["template"]["pagePath"]);
    
    if(is_dir($pagePath)){
        foreach(new \DirectoryIterator($pagePath) AS $fileInfo){
            if($fileInfo->isDot() || substr($fileInfo->getFilename(), -4) != ".php") continue;
            
            $value = $fileInfo->getFilename();
            $title = $fileInfo->getBasename(".php");
            
            $arTemplatePages[$arTemplate["template_id"]][$value] = $title;
        }
    }
    
    $arTemplateLayouts[$arTemplate["template_id"]] = array("" => "Не выбран");    
    
    $layoutPath = CFile::normalizePath(ROOT_PATH . $arConfig["template"]["path"] . "/" . $arTemplate["path"] . "/" . $arConfig["template"]["layoutPath"]);
    
    if(is_dir($layoutPath)){
        foreach(new \DirectoryIterator($layoutPath) AS $fileInfo){
            if($fileInfo->isDot() || substr($fileInfo->getFilename(), -4) != ".php") continue;
            
            $value = $fileInfo->getFilename();
            $title = $fileInfo->getBasename(".php");
            
            $arTemplateLayouts[$arTemplate["template_id"]][$value] = $title;
        }
    }
}

$arSitesOptionList = array();

foreach(CSite::getList() AS $arSite){
    $arSitesOptionList[$arSite["site_id"]] = "[" . $arSite["site_id"] . "] " . $arSite["title"];
}

\Page\Breadcrumbs::add(array(
    $listURL    => "Список страниц",
    $addURL     => "Добавление страницы"
));

$this->setData(array(
    "arSitesOptionList"     => $arSitesOptionList,
    "arTemplateLayouts"     => $arTemplateLayouts,
    "arTemplatePages"       => $arTemplatePages,
    "arTemplatesOptionList" => $arTemplatesOptionList,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "addURL"                => $addURL,
    "arFormData"            => $arFormData
));

$this->includeView("add");
?>