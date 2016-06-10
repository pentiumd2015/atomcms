<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$arFormData = isset($_REQUEST["template"]) ? $_REQUEST["template"] : array();

$arErrors = array();

if($_REQUEST["template"]){
    $arSaveResult = CTemplate::add($_REQUEST["template"]);
    
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
    $listURL => "Список шаблонов",
    $editURL => "Добавление шаблона"
));

$this->setData(array(
    "arErrors"  => $arErrors,
    "listURL"   => $listURL,
    "arFormData"=> $arFormData,
    "templatePath"  => \Helpers\CFile::normalizePath($this->app("template")->templatePath . "/{TEMPLATE_PATH}")
));

$this->includeView("add");
?>