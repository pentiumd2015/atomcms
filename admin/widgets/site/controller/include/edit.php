<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$siteID     = $obRoute->getVarValue("ID");

$arSites    = CSite::getList();
$arSite     = $arSites[$siteID];

if(!$arSite){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["site"]) ? $_REQUEST["site"] : $arSite;

$arErrors = array();

if($_REQUEST["site"]){
    $arSaveResult = CSite::update($siteID, $_REQUEST["site"]);
    
    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $arSaveResult["id"];
            $arResponse["redirectURL"]  = str_replace("{ID}", $arSaveResult["id"], $editURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $editURL = str_replace("{ID}", $arSaveResult["id"], $editURL);
            
            CHttpResponse::redirect($editURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
   }
}

\Page\Breadcrumbs::add(array(
    $listURL => "Список сайтов",
    $editURL => "Редактирование сайта"
));

$this->setData(array(
    "arErrors"  => $arErrors,
    "listURL"   => $listURL,
    "editURL"   => str_replace("{ID}", $arSite["site_id"], $editURL),
    "arFormData"=> $arFormData
));

$this->includeView("edit");
?>