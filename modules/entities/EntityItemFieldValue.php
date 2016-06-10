<?
namespace Entities;

class EntityItemFieldValue extends \DB\Manager{
    static protected $_table    = "entity_item_field_value";
    static protected $_pk       = "entity_item_field_value_id";
    
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityItemFieldValueID, $arData){
        return static::_save($arData, $entityItemFieldValueID);
    }
    
    static public function deleteByPk($entityItemFieldValueID){
        $result = parent::deleteByPk($entityItemFieldValueID);
        
        \CEvent::trigger("ENTITY.ITEM.FIELD.DELETE", array($entityItemFieldValueID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityItemFieldValueID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$entityItemFieldValueID){
            if(empty($arData["entity_item_id"])){
                $arErrors["entity_item_id"][] = "Значение должно быть привязано к элементу";
            }
            
            if(empty($arData["entity_field_id"])){
                $arErrors["entity_field_id"][] = "Значение должно быть привязано к полю";
            }
        }
        
        if(!count($arErrors)){
            $arData = static::getSafeFields($arData, array(
                "entity_item_id",
                "entity_field_id",
                "value_string",
                "value_text",
                "value_num"
            ));
            
            if($entityItemFieldValueID){
                parent::updateByPk($entityItemFieldValueID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.ITEM.FIELD.VALUE.UPDATE", array($entityItemFieldValueID, $arData));
            }else{
                $entityItemFieldValueID = parent::add($arData);
                
                if($entityItemFieldValueID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.ITEM.FIELD.VALUE.ADD", array($entityItemFieldValueID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityItemFieldValueID){
                $arReturn["id"] = $entityItemFieldValueID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
    
    static public function checkValues($arFieldValues){
        $arReturn               = array();
        $arReturn["success"]    = true;
        $arEntityFields         = EntityField::findAllByPk(array_keys($arFieldValues));
        $arEntityFields         = \Helpers\CArrayHelper::index($arEntityFields, "entity_field_id");
        
        $arErrors = array();
        
        foreach($arFieldValues AS $fieldID => $arValues){
            $obField = $arEntityFields[$fieldID];
            
            if($obField){
                $arFieldValues = array();
                
                foreach($arValues AS $valueID => $value){
                    if($valueID == "n"){ //если новые значения
                        if(is_array($value)){
                            $arValue = $value;
                            
                            foreach($arValue AS $value){
                                $arFieldValues[] = array("value" => $value);
                            }
                        }else{
                            $arFieldValues[] = array("value" => $value);
                        }
                    }else{
                        $arFieldValues[] = array(
                            "value"                     => $value,
                            "entity_item_field_value_id"=> $valueID
                        );
                    }
                }
                
                $fieldTypeClass     = EntityField::getType($obField->type);
                $obEntityFieldType  = new $fieldTypeClass($obField);
                $arFieldReturn      = $obEntityFieldType->checkValues($arFieldValues);
                
                if(!$arFieldReturn["success"]){
                    $arErrors[$obField->entity_field_id] = $arFieldReturn["errors"];
                }
            }
        }
        
        if(count($arErrors)){
            $arReturn["success"]= false;
            $arReturn["errors"] = $arErrors;
        }
        
        return $arReturn;
    }
    
    static public function setValues($entityItemID, $arFields){
        $arEntityFields = EntityField::findAllByPk(array_keys($arFields));
        $arEntityFields = \Helpers\CArrayHelper::index($arEntityFields, "entity_field_id");
        
        $arErrors = array();
        
        foreach($arFields AS $fieldID => $arValues){
            $obField = $arEntityFields[$fieldID];
            
            if($obField){
                $arFieldValues = array();
                
                foreach($arValues AS $valueID => $value){
                    if($valueID == "n"){ //если новые значения
                        if(is_array($value)){
                            $arValue = $value;
                            
                            foreach($arValue AS $value){
                                $arFieldValues[] = array("value" => $value);
                            }
                        }else{
                            $arFieldValues[] = array("value" => $value);
                        }
                    }else{
                        $arFieldValues[] = array(
                            "value"                         => $value,
                            "entity_item_field_value_id"    => $valueID
                        );
                    }
                }
                
                $fieldTypeClass     = EntityField::getType($obField->type);
                $obEntityFieldType  = new $fieldTypeClass($obField);
                $obEntityFieldType->setValues($entityItemID, $arFieldValues);
            }
        }
    }
}
?>