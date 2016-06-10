 <?
use \Helpers\CHttpResponse;
use \Entities\EntityItem;
use \Entities\EntityAdminView;
use \Helpers\CArrayHelper;

$method = $_REQUEST["method"];

switch($method){
    case "saveViewSettings":
        $arResponse     = array("result" => 1);
        $entityItemID   = (int)$_REQUEST["entityItemID"];
        
        if($entityItemID){
            $obEntityItem = EntityItem::findByPk($entityItemID);
            
            $arData = $_REQUEST["data"];
            
            if($obEntityItem){
                if(count($arData)){
                    //EntityAdminView::
                    
                    $arResponse["hasErrors"] = 0;
                }else{
                    $arResponse["hasErrors"]    = 1;
                    $arResponse["errors"]       = "Должна быть хотя бы одна вкладка";
                    $arResponse["error_code"]   = "empty view";
                }
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Элемент не найден";
                $arResponse["error_code"]   = "entity item not found";
            }
        }
        $arResponse["hasErrors"]    = 1;
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CHttpResponse::toJSON($arResponse);
        break;
}

exit;
?>