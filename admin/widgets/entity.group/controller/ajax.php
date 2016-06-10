 <?
use \Helpers\CHttpResponse;
use \Entities\EntityGroup;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "removeEntityGroup":
        $arResponse = array("result" => 1);
        $entityGroupID   = (int)$_REQUEST["entityGroupID"];
        
        if($entityGroupID){
            $obEntityGroup = EntityGroup::findByPk($entityGroupID);
            
            if($obEntityGroup){
                EntityGroup::deleteByPk($obEntityGroup->entity_group_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Группа не найдена";
                $arResponse["error_code"]   = "entity group not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>