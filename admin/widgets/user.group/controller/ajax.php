 <?
use \Helpers\CHttpResponse;
use \Models\UserGroup;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeUserGroup":
        $arResponse = array("result" => 1);
        $userGroupID = (int)$_REQUEST["userGroupID"];
        
        if($userGroupID){
            $obUserGroup = UserGroup::findByPk($userGroupID);
            
            if($obUserGroup){
                UserGroup::deleteByPk($obUserGroup->user_group_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Группа не найдена";
                $arResponse["error_code"]   = "user group not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>