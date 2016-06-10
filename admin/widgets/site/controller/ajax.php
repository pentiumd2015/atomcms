 <?
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeSite":
        $arResponse = array("result" => 1);
        $siteID     = $_REQUEST["siteID"];
        
        $result = CSite::delete($siteID);
            
        if($result){
            $arResponse["hasErrors"]    = 0;
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = "Сайт не найден";
            $arResponse["error_code"]   = "site not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>