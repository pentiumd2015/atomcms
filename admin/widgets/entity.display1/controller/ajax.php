<?
use \Entities\EntityAdminDisplay;
use \Entities\EntitySection;
use \Entities\EntityItem;
use \Helpers\CHttpResponse;
use \Entities\Entity;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

$userID = $this->app("user")->getID();

switch($method){
    case "saveViewSettings":
        $arResponse         = array("result" => 1);
        $entityID           = (int)$_REQUEST["entityID"];
        $entitySectionID    = (int)$_REQUEST["entitySectionID"];
        $relation           = $_REQUEST["relation"];
        $type               = $_REQUEST["type"] == "list" ? "list" : "detail" ;

        if($entityID){
            $obEntity = Entity::findByPk($entityID);
            
            $arData = $_REQUEST["data"];
            
            if($obEntity){
                if(count($arData)){
                    EntityAdminDisplay::setDisplayMap($entityID, $entitySectionID, $userID, $relation, $type, $arData);
                    
                    $arResponse["hasErrors"] = 0;
                }else{
                    $arResponse["hasErrors"]    = 1;
                    $arResponse["errors"]       = "Должно быть хотя бы одно поле";
                    $arResponse["error_code"]   = "empty display";
                }
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Элемент не найден";
                $arResponse["error_code"]   = "entity item not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "removeEntityDisplay":
        $arResponse = array("result" => 1);
        $entitySectionID    = (int)$_REQUEST["entitySectionID"];
        $relation           = $_REQUEST["relation"];
        $type               = $_REQUEST["type"] == "list" ? "list" : "detail" ;
        
        if($entitySectionID){
            $obEntitySection = EntityItem::find("entity_item_id=? AND type=?", array($entitySectionID, EntityItem::TYPE_SECTION));

            if($obEntitySection){
                EntityAdminDisplay::deleteDisplayMap($obEntitySection->entity_id, $entitySectionID, $userID, $relation, $type);
            }
            
            $arResponse["hasErrors"]    = 0;
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = "Раздел не найден";
            $arResponse["error_code"]   = "entity section not found";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>