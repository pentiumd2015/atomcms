<?
use \Helpers\CHttpResponse;
use \Helpers\CHttpRequest;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;
use \Helpers\CFile;

$siteID         = $_REQUEST["site_id"];
$routePath      = $_REQUEST["path"];

$arRoutes    = CRouter::getList($siteID);
$arRoute     = $arRoutes[$routePath];

if(!$arRoute){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["route"]) ? $_REQUEST["route"] : $arRoute;

$arErrors = array();

if($_REQUEST["route"]){
    $arData = $_REQUEST["route"];
    $arData["site_id"] = $siteID;
    
    if(!is_array($arData["templates"])){
        $arData["templates"] = array();
    }
    
    $priority = 0;
    
    foreach($arData["templates"] AS &$arTemplateItem){
        $arTemplateItem["priority"] = $priority;
        $priority++;
    }
    
    unset($arTemplateItem);
    
    $arSaveResult = CRouter::update($siteID, $routePath, $arData);
    
    if(CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = $editURL . "?" . CHttpRequest::toQuery(array(
                "site_id"   => urlencode($siteID), 
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
                "site_id"   => urlencode($siteID), 
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

$editURL.= "?" . CHttpRequest::toQuery(array(
    "site_id"   => urlencode($siteID), 
    "path"      => urlencode($routePath)
));

\Page\Breadcrumbs::add(array(
    $listURL => "Список страниц",
    $editURL => "Редактирование страницы"
));

$this->setData(array(
    "arTemplateLayouts"     => $arTemplateLayouts,
    "arTemplatePages"       => $arTemplatePages,
    "arTemplatesOptionList" => $arTemplatesOptionList,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "editURL"               => $editURL,
    "arFormData"            => $arFormData
));

$this->includeView("edit");
?>