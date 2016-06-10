<?
use \Models\UserGroupAccess;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$userGroupAccessID = (int)$obRoute->getVarValue("ID");

$arUserGroupAccess = UserGroupAccess::findByPk($userGroupAccessID);

if(!$arUserGroupAccess){
    CHttpResponse::redirect("/404/");
}

$arFormData = isset($_REQUEST["user_group_access"]) ? $_REQUEST["user_group_access"] : $arUserGroupAccess;

$arErrors = array();

if($_REQUEST["user_group_access"]){
    $arSaveResult = UserGroupAccess::updateByPk($arUserGroupAccess["user_group_access_id"], $_REQUEST["user_group_access"]);
    
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
    $addURL     => "Редактирование правила"
));

$this->setData(array(
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "editURL"               => str_replace("{USER_GROUP_ACCESS_ID}", $arUserGroupAccess["user_group_access_id"], $editURL),
    "arFormData"            => $arFormData
));

$this->includeView("edit");
?>