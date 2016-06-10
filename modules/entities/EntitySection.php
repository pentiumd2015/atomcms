<?
namespace Entities;

class EntitySection extends EntityItem{    
    static public function add($arData){
        $arData["type"] = static::TYPE_SECTION;
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entitySectionID, $arData){
        return static::_save($arData, $entitySectionID);
    }

    static public function deleteByPk($entitySectionID){
        $result = parent::deleteByPk($entitySectionID);
        
        EntitySectionTree::deleteByPk($entitySectionID);
        
        \CEvent::trigger("ENTITY.SECTION.DELETE", array($entitySectionID));
        
        return $result;
    }
    
    static public function findAll($arParams = array(), $arValues = array()){
        if(is_string($arParams)){
            $arParams = array(
                "condition" => $arParams
            );
        }
        
        $condition              = $arParams["condition"];
        $arParams["condition"]  = ($arParams["alias"] ? $arParams["alias"] . "." : "") . "type=?";
        
        array_unshift($arValues, static::TYPE_SECTION);
        
        if(strlen($condition)){
            $arParams["condition"].= " AND " . $condition;
        }
        
        return parent::findAll($arParams, $arValues);
    }
    
    static public function find($arParams = array(), $arValues = array()){
        if(is_string($arParams)){
            $arParams = array(
                "condition" => $arParams
            );
        }

        $condition              = $arParams["condition"];
        $arParams["condition"]  = ($arParams["alias"] ? $arParams["alias"] . "." : "") . "type=?";
        
        array_unshift($arValues, static::TYPE_SECTION);
        
        if(strlen($condition)){
            $arParams["condition"].= " AND " . $condition;
        }
      
        return parent::find($arParams, $arValues);
    }
    
    static protected function _save($arData, $entitySectionID = false){
        $arResult = parent::_save($arData, $entitySectionID);
        
        if($arResult["success"]){
            if(!$entitySectionID){ //if add
                $entitySectionID = $arResult["itemID"];
                
                EntitySectionTree::add(array(
                    "entity_item_id"    => $entitySectionID,
                    "parent_id"         => $arData["parent_id"]
                ));
            }else{
                if($arData["parent_id"]){
                    EntitySectionTree::setParent($entitySectionID, $arData["parent_id"]);
                }
            }
            
            if($entitySectionID){
                $arFieldValues = array();
                
                foreach($arData AS $field => $arValues){
                    if(is_numeric($field)){
                        $arFieldValues[(int)$field] = $arValues;
                    }
                }
                
                /*set field values*/
                if(count($arFieldValues)){
                    EntitySectionFieldValue::setValues($entitySectionID, $arFieldValues);
                }
                /*set field values*/
            }
        }
        
        return $arResult;
    }
}
?>