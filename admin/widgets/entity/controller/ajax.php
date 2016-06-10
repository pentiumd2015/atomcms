<?
use \Entity\Display;
use \Entity\ExtraField;
use \Entity\Field\Field;

$method = $_REQUEST["method"];
$userID = $this->app("user")->getID();

switch($method){
    case "addField":
        $arResponse  = array("result" => 1);
        $entityClass = $_REQUEST["entity"];
        
        if($entityClass && class_exists($entityClass)){
            $arFieldTypes = ExtraField::getFieldTypes();
            
            $arFieldTypeList = array();
            
            foreach($arFieldTypes AS $fieldTypeClass => $obFieldType){
                $arInfo = $obFieldType->getInfo();
                
                $arFieldTypeList[$fieldTypeClass] = $arInfo["title"];
            }
            
            $obFieldType = reset($arFieldTypes);
            
            $this->setData(array(
                "arData"            => array(),
                "arFieldTypeList"   => $arFieldTypeList,
                "arFieldTypes"      => $arFieldTypes,
                "obFieldType"       => $obFieldType,
                "entityClass"       => $entityClass
            ));
            
            $arResponse["content"] = array(
                "title"     => "Новое поле",
                "body"      => $this->getViewContent("editField"),
                "buttons"   => array(
                    CHtml::button("<i class=\"icon-checkmark\"></i> Применить", array(
                        "class" => "btn btn-primary",
                        "onclick" => "$.entityFieldParams().saveSettings();"
                    )),
                    CHtml::button("Отмена", array(
                        "class"     => "btn btn-danger",
                        "data-mode" => "close"
                    )),
                )
            );
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "editField":
        $arResponse  = array("result" => 1);
        $entityClass = $_REQUEST["entity"];
        $fieldID     = $_REQUEST["fieldID"];

        if($entityClass && class_exists($entityClass)){
            $obEntity = new $entityClass;
            $arFieldTypes = ExtraField::getFieldTypes();
            
            $arFieldTypeList = array();
            
            foreach($arFieldTypes AS $fieldTypeClass => $obFieldType){
                $arInfo = $obFieldType->getInfo();
                
                $arFieldTypeList[$fieldTypeClass] = $arInfo["title"];
            }
            
            $arFieldTypeList["custom"] = "Кастомное поле";
            
            $arData = ExtraField::getByID($fieldID);
            
            if($arData["type"]){
                if(isset($arFieldTypes[$arData["type"]])){
                    $obFieldType = $arFieldTypes[$arData["type"]];
                }
            }else{
                $obFieldType = reset($arFieldTypes);
            }
            
            $this->setData(array(
                "arData"            => $arData,
                "arFieldTypeList"   => $arFieldTypeList,
                "arFieldTypes"      => $arFieldTypes,
                "obFieldType"       => $obFieldType,
                "entityClass"       => $entityClass,
                "fieldID"           => $fieldID,
                "fieldPk"           => ExtraField::getPk()
            ));
            
            $arResponse["content"] = array(
                "title"     => "Поле " . $arExtraField["title"],
                "body"      => $this->getViewContent("editField"),
                "buttons"   => array(
                    CHtml::button("<i class=\"icon-checkmark\"></i> Применить", array(
                        "class" => "btn btn-primary",
                        "onclick" => "$.entityFieldParams().saveSettings();"
                    )),
                    CHtml::button("Отмена", array(
                        "class"     => "btn btn-danger",
                        "data-mode" => "close"
                    )),
                )
            );
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "deleteField":
        $arResponse     = array("result" => 1);
        $fieldID        = (int)$_REQUEST["fieldID"];
        
        ExtraField::delete($fieldID);
        
        $arResponse["hasErrors"] = 0;
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "getFieldParamsHtml":
        $arResponse     = array("result" => 1);
        $fieldID        = (int)$_REQUEST["fieldID"];
        $arExtraField   = $_REQUEST["entity_field"];
        $fieldTypeClass = $arExtraField["type"];
        
        $entityClass = $_REQUEST["entity"];
        
        if($entityClass && class_exists($entityClass)){
            $obEntity = new $entityClass;
            
            if($fieldID){ //TO DO check access of current user
            /*    $obEntityField = EntityField::findByPk($entityFieldID);
                
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
                }*/
            }else{
                if(class_exists($fieldTypeClass)){
                    $obFieldType = new $fieldTypeClass("", $arExtraField, $obEntity);
                    
                    $arResponse["hasErrors"]= 0;
                    $arResponse["html"]     = $obFieldType->getRenderer()->renderParams();
                }else{
                    $arResponse["hasErrors"]    = 1;
                    $arResponse["errors"]       = "Тип поля не найден";
                    $arResponse["error_code"]   = "field type not found";
                }
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    /*case "removeEntityField":
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
        break;*/
    case "saveFieldSettings":
        $arResponse     = array("result" => 1);
        $entityClass    = $_REQUEST["entity"];
        $arExtraField   = $_REQUEST["entity_field"];
        
        if($entityClass && class_exists($entityClass)){
            $obEntity = new $entityClass;
            
            $arExtraField["entity_id"] = $obEntity->getEntityName();
            
            $fieldPk = ExtraField::getPk();
            $fieldID = $arExtraField[$fieldPk];
            
            if($fieldID){
                $obResult = ExtraField::update($fieldID, $arExtraField);
            }else{
                $obResult = ExtraField::add($arExtraField);
            }

            if($obResult->isSuccess()){
                $arResponse["hasErrors"] = 0;
                $arResponse["id"] = $obResult->getID();
            }else{
                $arResponse["hasErrors"] = 1;
                $arResponse["errors"]       = array();
                
                foreach($obResult->getErrors() AS $obFieldError){
                    $arResponse["errors"][$obFieldError->getFieldName()] = array(
                        "code"      => $obFieldError->getCode(),
                        "message"   => $obFieldError->getMessage()
                    );
                }
            }
        }else{
            $arResponse["hasErrors"] = 1;
            $arResponse["errors"] = array("Сущность " . $obEntity->getClass() . " не найдена");
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;/**/
}

exit;
?>