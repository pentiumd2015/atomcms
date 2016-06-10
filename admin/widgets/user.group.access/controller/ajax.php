 <?
use \Helpers\CHttpResponse;
use \Models\UserGroupAccess;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeUserGroupAccess":
        $arResponse = array("result" => 1);
        $userGroupAccessID   = (int)$_REQUEST["userGroupAccessID"];
        
        if($userGroupAccessID){
            $obUserGroupAccess = UserGroupAccess::findByPk($userGroupAccessID);
            
            if($obUserGroupAccess){
                UserGroupAccess::deleteByPk($obUserGroupAccess->user_group_access_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Правило не найдено";
                $arResponse["error_code"]   = "user group access not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>