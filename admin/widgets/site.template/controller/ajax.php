 <?
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeTemplate":
        $arResponse = array("result" => 1);
        $templateID     = $_REQUEST["templateID"];
        
        $result = CTemplate::delete($templateID);
            
        if($result){
            $arResponse["hasErrors"]    = 0;
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = "Шаблон не найден";
            $arResponse["error_code"]   = "template not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>