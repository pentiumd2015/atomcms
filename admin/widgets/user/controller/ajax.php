<?
use Helpers\CHttpResponse;
use Helpers\CJson;

$request    = CAtom::$app->request;
$method     = $request->get("method");

switch($method){
    case "remove":
        $response   = ["result" => 1];
        $id         = (int)$request->get("id");
        
        if($id && $id > 1){
            CUser::delete($id);

            $response["hasErrors"]    = 0;
        }else{
            $response["hasErrors"]    = 1;
            $response["errors"]       = "Пользователь не найден";
            $response["error_code"]   = "user not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJson::encode($response);
        break;
}