 <?
use \Helpers\CHttpResponse;
use \Entities\Entity;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeEntity":
        $arResponse = array("result" => 1);
        $entityID   = (int)$_REQUEST["entityID"];
        
        if($entityID){
            $obEntity = Entity::findByPk($entityID);
            
            if($obEntity){
                Entity::deleteByPk($obEntity->entity_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Сущность не найдена";
                $arResponse["error_code"]   = "entity not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>