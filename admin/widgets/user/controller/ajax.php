 <?
use \Helpers\CHttpResponse;
use \Models\User;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "remove":
        $arResponse = array("result" => 1);
        $id         = (int)$_REQUEST["id"];
        
        if($id){
            $arUser = User::getByID($id);
            
            if($id > 1 && $arUser){
                User::delete($arUser[User::getPk()]);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Пользователь не найден";
                $arResponse["error_code"]   = "user not found";
            }
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = "Пользователь не найден";
            $arResponse["error_code"]   = "user not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>