 <?
use \Helpers\CHttpResponse;
use \Entities\EntityField;
use \Entities\EntityFieldVariant;
use \Entities\Entity;
use \Helpers\CJSON;

$method = $_REQUEST["method"];

switch($method){
    case "getFieldParamsHtml":
        $arResponse         = array("result" => 1);
        $entityFieldID      = (int)$_REQUEST["entityFieldID"];
        $arEntityField      = $_REQUEST["entity_field"];
        $entityFieldType    = (int)$arEntityField["type"];
        
        if($entityFieldID){ //TO DO check access of current user
            $obEntityField = EntityField::findByPk($entityFieldID);
            
            if($obEntityField){
                $obEntityField->params  = CJSON::decode($obEntityField->params, true);
                $obFieldType            = EntityField::getType($obEntityField); //get object field type
                $obNewEntityField       = clone $obEntityField;
                $obNewEntityField->type = $entityFieldType;
                $obNewFieldType         = EntityField::getType($obNewEntityField); //get request field type
                 
                if(!$obNewFieldType){
                    $arResponse["hasErrors"]    = 1;
                    $arResponse["errors"]       = "Тип поля не найден";
                    $arResponse["error_code"]   = "field type not found";
                }else{
                    if($obEntityField->type != $obNewEntityField->type){ //if type has changed
                        $obFieldType = $obNewFieldType;
                    }
                    
                    $arEntityField["entity_field_id"] = $entityFieldID;
                    
                    $arFieldVariants = EntityFieldVariant::findAll(array(
                        "condition" => "entity_field_id=?",
                        "order"     => "priority ASC"
                    ), array($entityFieldID));
                    
                    $obFieldType->setData(array(
                        "arRequestField"    => $arEntityField,
                        "arFieldVariants"   => $arFieldVariants
                    ));
                    
                    $arResponse["hasErrors"]    = 0;
                    $arResponse["html"] = $obFieldType->renderParams();
                }
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Поле не найдено";
                $arResponse["error_code"]   = "field not found";
            }
        }else{ //if new field
            $obEntityField = new \stdClass;
            $obEntityField->type = $entityFieldType;
            
            $obFieldType = EntityField::getType($obEntityField); //get object field type
            
            if(!$obFieldType){
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Тип поля не найден";
                $arResponse["error_code"]   = "field type not found";
            }else{
                $obFieldType->setData($arEntityField);
                
                $arResponse["hasErrors"]    = 0;
                $arResponse["html"] = $obFieldType->renderParams();
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "removeEntityField":
        $arResponse = array("result" => 1);
        $entityFieldID   = (int)$_REQUEST["entityFieldID"];
        
        if($entityFieldID){
            $obEntityField = EntityField::findByPk($entityFieldID);
            
            if($obEntityField){
                EntityField::deleteByPk($obEntityField->entity_field_id);
                
                $arResponse["hasErrors"]    = 0;
            }else{
                $arResponse["hasErrors"]    = 1;
                $arResponse["errors"]       = "Поле не найдено";
                $arResponse["error_code"]   = "entity field not found";
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>