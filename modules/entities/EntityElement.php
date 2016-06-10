<?
namespace Entities;

class EntityElement extends EntityItem{
    static public function add($arData){
        $arData["type"] = static::TYPE_ELEMENT;
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityElementID, $arData){
        return static::_save($arData, $entityElementID);
    }
    
    static public function deleteByPk($entityElementID){
        $result = parent::deleteByPk($entityElementID);
        
        \CEvent::trigger("ENTITY.ELEMENT.DELETE", array($entityElementID));
        
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
        
        array_unshift($arValues, static::TYPE_ELEMENT);
        
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
        
        array_unshift($arValues, static::TYPE_ELEMENT);
        
        if(strlen($condition)){
            $arParams["condition"].= " AND " . $condition;
        }
      
        return parent::find($arParams, $arValues);
    }
    
    static protected function _save($arData, $entityElementID = false){
        $arResult = parent::_save($arData, $entityElementID);
        
        if($arResult["success"]){
            if(!$entityElementID){ //if add
                $entityElementID = $arResult["itemID"];
            }
            
            /*element sections*/
            if($arData["sections"]){
                EntitySectionElement::setValues($entityElementID, $arData["sections"]);
            }
            /*element sections*/
            
            $arFieldValues = array();
            
            foreach($arData AS $field => $arValues){
                if(is_numeric($field)){
                    $arFieldValues[(int)$field] = $arValues;
                }
            }
            
            /*set field values*/
            if(count($arFieldValues)){
                EntityElementFieldValue::setValues($entityElementID, $arFieldValues);
            }
            /*set field values*/
        }
        
        return $arResult;
    }
}
?>