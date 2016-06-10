<?
namespace Entities;

class EntityFieldVariant extends \DB\Manager{
    static protected $_table    = "entity_field_variant";
    static protected $_pk       = "entity_field_variant_id";
    
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityFieldVariantID, $arData){
        return static::_save($arData, $entityFieldVariantID);
    }
    
    static public function deleteByPk($entityFieldVariantID){
        $result = parent::deleteByPk($entityFieldVariantID);
        
        \CEvent::trigger("ENTITY.FIELD.VARIANT.DELETE", array($entityFieldVariantID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityFieldVariantID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$entityFieldVariantID){
            if(empty($arData["entity_field_id"])){
                $arErrors["entity_field_id"][] = "Значение должно быть привязано к полю";
            }
        }
        
        if(!count($arErrors)){
            $arData["priority"] = (int)$arData["priority"];
            
            $arData = static::getSafeFields($arData, array(
                "entity_field_id",
                "title",
                "priority"
            ));
            
            if($entityFieldVariantID){
                parent::updateByPk($entityFieldVariantID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.FIELD.VARIANT.UPDATE", array($entityFieldVariantID, $arData));
            }else{
                $entityFieldVariantID = parent::add($arData);
                
                if($entityFieldVariantID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.FIELD.VARIANT.ADD", array($entityFieldVariantID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityFieldVariantID){
                $arReturn["id"] = $entityFieldVariantID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
    
    static public function setValues($entityFieldID, $arVariantValues){
        /*удаляем не переданные варианты*/
        $arFieldVariantIDs    = array();
        
        foreach($arVariantValues AS $valueID => $arVariantValue){
            if($valueID != "n"){
                $arFieldVariantIDs[] = $valueID;
            }
        }
        
        //удаляем значения, которые не были переданы
        $deleteSQL = "t1.entity_field_id=?";
        
        if(count($arFieldVariantIDs)){
            $deleteSQL.= " AND t1.entity_field_variant_id NOT IN(" . implode(", ", $arFieldVariantIDs) . ")";
        }
        
        //также удаляем значения элементов для удаленных вариантов
        static::delete(
            array(
                "alias"     => "t1",
                "delete"    => "t1.*,t2.*",
                "join"      => "LEFT JOIN entity_item_field_value t2 ON(t1.entity_field_id=t2.entity_field_id AND t1.entity_field_variant_id=t2.value_num)",
                "condition" => $deleteSQL
            ), 
            array($entityFieldID)
        );
        /*удаляем не переданные варианты*/

        $priority = 0;
        
        foreach($arVariantValues AS $valueID => $arValue){
            if($valueID == "n"){ //если новые значения
                if(is_array($arValue)){
                    $arValues = $arValue;
                    
                    foreach($arValues AS $arValue){
                        if(!strlen($arValue["title"])){
                            continue;
                        }
                        
                        static::add(array(
                            "entity_field_id"   => $entityFieldID,
                            "title"             => $arValue["title"],
                            "priority"          => $priority
                        ));
                        
                        $priority++;
                    }
                }else{
                    if(!strlen($arValue["title"])){
                        continue;
                    }
                    
                    static::add(array(
                        "entity_field_id"   => $entityFieldID,
                        "title"             => $arValue["title"],
                        "priority"          => $priority
                    ));
                    
                    $priority++;
                }
            }else{
                if(!strlen($arValue["title"])){
                    continue;
                }
                
                static::updateByPk($valueID, array(
                    "title"             => $arValue["title"],
                    "priority"          => $priority
                ));
                
                $priority++;
            }
        }
    }
}
?>