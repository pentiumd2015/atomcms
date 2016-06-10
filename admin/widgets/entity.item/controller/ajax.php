 <?
use \Helpers\CHttpResponse;
use \Entities\EntitySection;
use \Entities\EntityElement;
use \Helpers\CArrayHelper;
use \Helpers\CJSON;

$method = $_REQUEST["method"];
$userID = $this->app("user")->getID();

switch($method){
    case "removeEntityElement":
        $arResponse     = array("result" => 1);
        $entityElementID   = (int)$_REQUEST["entityElementID"];
        
        if($entityElementID){
            $obEntityElement = EntityElement::findByPk($entityElementID);
            
            if($obEntityElement){
                EntityElement::deleteByPk($obEntityElement->entity_item_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Элемент не найден";
                $arResponse["error_code"]   = "entity element not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "removeEntitySection":
        $arResponse         = array("result" => 1);
        $entitySectionID    = (int)$_REQUEST["entitySectionID"];
        
        if($entitySectionID){
            $obEntitySection = EntitySection::findByPk($entitySectionID);
            
            if($obEntitySection){
                EntitySection::deleteByPk($obEntitySection->entity_item_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Раздел не найден";
                $arResponse["error_code"]   = "entity section not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>