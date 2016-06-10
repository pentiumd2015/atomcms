<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$templateID     = $obRoute->getVarValue("ID");

$arTemplates    = CTemplate::getList();
$arTemplate     = $arTemplates[$templateID];

if(!$arTemplate){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["template"]) ? $_REQUEST["template"] : $arTemplate;

$arErrors = array();

if($_REQUEST["template"]){
    $arSaveResult = CTemplate::update($templateID, $_REQUEST["template"]);
    
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
    $editURL => "Редактирование шаблона"
));

$this->setData(array(
    "arErrors"      => $arErrors,
    "listURL"       => $listURL,
    "editURL"       => str_replace("{ID}", $arTemplate["template_id"], $editURL),
    "arFormData"    => $arFormData,
    "templatePath"  => \Helpers\CFile::normalizePath($this->app("template")->templatePath . "/{TEMPLATE_PATH}")
));

$this->includeView("edit");
?>