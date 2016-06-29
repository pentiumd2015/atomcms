<?
use Helpers\CHttpResponse;
use Helpers\CJson;

$request    = CAtom::$app->request;
$method     = $request->get("method");

switch($method){
    case "remove":
        $response   = ["result" => 1];
        $id         = (int)$request->get("id");

        if($id){
            $userGroup = CUserGroup::getById($id);

            if($userGroup && !CUserGroup::isSystemGroup($userGroup["alias"])){
                CUserGroup::delete($id);

                $response["hasErrors"]    = 0;
            }else{
                $response["hasErrors"]    = 1;
                $response["errors"]       = "Нельзя удалить системную группу";
                $response["error_code"]   = "system group";
            }
        }else{
            $response["hasErrors"]    = 1;
            $response["errors"]       = "Группа не найдена";
            $response["error_code"]   = "user group not found";
        }

        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJson::encode($response);
        break;
}