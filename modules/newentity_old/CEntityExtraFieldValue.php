<?
namespace NewEntity;

class CEntityExtraFieldValue extends Manager{
    static protected $entityName    = "new_entity_extra_field_value";
    static protected $pk            = "id";
    
    static public function getBaseFields(){
        return array(
            "id" => array(
                "type"      => "integer",
                "primary"   => true
            ),
            "extra_field_id" => array(
                "type"      => "integer",
                "required"  => true
            ),
            "item_id" => array(
                "type"      => "integer",
                "required"  => true
            ),
            "value_num" => array(
                "type" => "integer"
            ),
            "value_string" => array(
                "type" => "string"
            ),
            "value_text" => array(
                "type" => "text"
            )
        );
    }
    /*
    static protected function validateFields($arData){
        $arErrors = array();
        
        foreach(static::getBaseFields() AS $fieldName => $arField){
            if(is_string($arField["type"])){
                $typeField          = $arField["type"];
                $typeField[0]       = strtoupper($typeField[0]);
                $type               = "\\" . __NAMESPACE__ . "\FieldType\\" . $typeField . "Field";
                $obFieldType        = new $type($fieldName, $arField);
                $arValidateResult   = $obFieldType->validate($arData);
                
                if(!$arValidateResult["success"]){
                    $arErrors[$fieldName] = $arValidateResult["error"];
                }
            }
        }
        
        $arResult = array();
        
        if(count($arErrors)){
            $arResult["success"]    = false;
            $arResult["errors"]     = $arErrors;
        }else{
            $arResult["success"]    = true;
        }
        
        return $arResult;
    }
    */
    static public function add($arData){
        $resField = CEntityExtraField::getByID($arData["extra_field_id"]);
        
        if($arField = $resField->fetch(\DB\Connection::FETCH_ASSOC)){
            $resFieldValues     = self::getList("extra_field_id=? AND item_id=?", array($arData["extra_field_id"], $arData["item_id"]));
            
            $arFieldValues      = $resFieldValues->fetchAll(\DB\Connection::FETCH_ASSOC);
            
            $obExtraFieldType   = ExtraFieldType\Field::getFieldType($arField["type"]);
            $storageType        = $obExtraFieldType->getStorageType();
            
            $arData["value_" . $storageType] = $arData["value"];
            
            $arFieldValues[] = $arData;
            
            $obExtraFieldType->setFieldData($arField);
            
            $arValidateResult = $obExtraFieldType->validate($arFieldValues);

            if(!$arValidateResult["success"]){
                $error = $arValidateResult["error"];
            }else{
                return parent::add($arData);
            }
        }else{
            $error = "not found";
        }
        
        $obResult = new Result\AddResult;
        $obResult->setSuccess(false);
        $obResult->setErrors($error);
        
        return $obResult;
    }
    /*
    static public function update($id, $arData){
        $resField = CEntityExtraField::getByID($arData["extra_field_id"]);
        
        $obResult = new Result\UpdateResult;
        
        if($arField = $resField->fetch(\DB\Connection::FETCH_ASSOC)){
            $resFieldValues     = self::getList("extra_field_id=? AND item_id=?", array($arData["extra_field_id"], $arData["item_id"]));
            
            $arFieldValues      = $resFieldValues->fetchAll(\DB\Connection::FETCH_ASSOC);
            
            $obExtraFieldType   = ExtraFieldType\Field::getFieldType($arField["type"]);
            $storageType        = $obExtraFieldType->getStorageType();
            
            $arData["value_" . $storageType] = $arData["value"];
            
            $arFieldValues[] = $arData;
            
            $obExtraFieldType->setFieldData($arField);
            
            $arValidateResult = $obExtraFieldType->validate($arFieldValues);

            if(!$arValidateResult["success"]){
                $obResult->setSuccess(false);
                $obResult->setErrors($arValidateResult["error"]);
            }else{
                return parent::update($id, $arData);
            }
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors(array(
                "id" => "not found"
            ));
        }
        
        return $obResult;
    }*/
    
    //TO DO сделать метод setValues() // заполняет только те свойства, которые были переданы, без удаления других
}
?>