 <?
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeRoute":
        $arResponse = array("result" => 1);
        list($siteID, $routePath) = explode("#", $_REQUEST["routeID"], 2);
        
        $result = CRouter::delete($siteID, $routePath);
            
        if($result){
            $arResponse["hasErrors"]    = 0;
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = "Страница не найдена";
            $arResponse["error_code"]   = "route not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>