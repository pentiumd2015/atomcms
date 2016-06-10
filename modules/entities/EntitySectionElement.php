<?
namespace Entities;

class EntitySectionElement extends \DB\Manager{
    static protected $_table    = "entity_section_element";
    static protected $_pk       = "entity_section_element_id";
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entitySectionElementID, $arData){
        return static::_save($arData, $entitySectionElementID);
    }
    
    static public function deleteByPk($entitySectionElementID){
        $result = parent::deleteByPk($entitySectionElementID);
        
        \CEvent::trigger("ENTITY.SECTION_ELEMENT.DELETE", array($entitySectionElementID));
        
        return $result;
    }
    
    static protected function _save($arData, $entitySectionElementID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
    
        if(!$entitySectionElementID){
            if(empty($arData["entity_section_id"])){
                $arErrors["entity_section_id"][] = "Выберите раздел для привязки";
            }
            
            if(empty($arData["entity_element_id"])){
                $arErrors["entity_element_id"][] = "Выберите элемент для привязки";
            }
        }else{
            if(isset($arData["entity_section_id"]) && empty($arData["entity_section_id"])){
                $arErrors["entity_section_id"][] = "Выберите раздел для привязки";
            }
            
            if(isset($arData["entity_element_id"]) && empty($arData["entity_element_id"])){
                $arErrors["entity_element_id"][] = "Выберите элемент для привязки";
            }
        }
    
        if(!count($arErrors)){
            $arData = static::getSafeFields($arData, array(
                "entity_section_id",
                "entity_element_id"
            ));
            
            if($entitySectionElementID){
                parent::updateByPk($entitySectionElementID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.SECTION_ELEMENT.UPDATE", array($entitySectionElementID, $arData));
            }else{
                $entitySectionElementID = parent::add($arData);
                
                if($entitySectionElementID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.SECTION_ELEMENT.ADD", array($entitySectionElementID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entitySectionElementID){
                $arReturn["id"] = $entitySectionElementID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
    
    static public function setValues($entityElementID, $arSectionIDs){
        array_walk($arSectionIDs, "intval");
        
        $arSectionIDs = array_filter($arSectionIDs);
        
        $sectionSQL = "entity_element_id=?";
        
        if(count($arSectionIDs)){
            $sectionSQL.= " AND entity_section_id NOT IN(" . implode(", ", $arSectionIDs) . ")";
        }
        
        static::delete($sectionSQL, array($entityElementID));
        
        $arSectionItems = static::findAll("entity_element_id=?", array($entityElementID));
        $arSectionItems = \Helpers\CArrayHelper::index($arSectionItems, "entity_section_id");

        foreach($arSectionIDs AS $sectionID){
            if(!isset($arSectionItems[$sectionID])){
                static::add(array(
                    "entity_element_id" => $entityElementID,
                    "entity_section_id" => $sectionID
                ));
            }
        }
    }
}
?>