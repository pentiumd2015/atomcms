<?
use \Models\UserGroupAccess;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$arFormData = isset($_REQUEST["user_group_access"]) ? $_REQUEST["user_group_access"] : array();

$arErrors = array();

if($_REQUEST["user_group_access"]){
    $arSaveResult = UserGroupAccess::add($_REQUEST["user_group_access"]);

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
    $listURL    => "Список правил",
    $addURL     => "Новое правило"
));

$this->setData(array(
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "arFormData"            => $arFormData
));

$this->includeView("add");
?>