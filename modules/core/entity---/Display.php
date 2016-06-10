<?
namespace Entity;

use \CJSON;
use \CArrayHelper;

class Display extends Entity{
    static protected $_table    = "new_entity_display";
    static protected $_pk       = "id";
          
    static protected $arEvents = array(
        "ADD"       => "ENTITY.DISPLAY.ADD",
        "UPDATE"    => "ENTITY.DISPLAY.UPDATE",
        "DELETE"    => "ENTITY.DISPLAY.DELETE",
    );
    
    public function getFields(){
        return array(
            new Field\IntegerField("id", array(
                "title"     => "ID",
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new Field\StringField("entity_id", array(
                "title"     => "Entity ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new Field\TextField("data", array(
                "title"     => "Data",
                "required"  => false,
                "visible"   => false,
                "disabled"  => true
            ), $this),
            new Field\IntegerField("user_id", array(
                "title"     => "User ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            ), $this),
        );
    }
    
    static public function getAllFields(Entity $obEntity){
        $arAllFields = array();
        
        foreach($obEntity->getFields() AS $obField){
            if($obField && $obField->isVisible()){
                $arAllFields[$obField->getFieldName()] = $obField;
            }
        }
        
        foreach($obEntity->getCustomFields() AS $obCustomField){
            if($obCustomField && $obCustomField->isVisible()){
                $arAllFields[$obCustomField->getFieldName()] = $obCustomField;
            }
        }
        
        $fieldPk = ExtraField::getPk();
        
        foreach($obEntity->getExtraFields() AS $arExtraField){
            $fieldTypeClass = $arExtraField["type"];
            
            if(class_exists($fieldTypeClass)){
                $fieldName  = ExtraField::getFieldNameById($arExtraField[$fieldPk]);
                $obField    = new $fieldTypeClass($fieldName, $arExtraField, $obEntity);

                if($obField && $obField->isVisible()){
                    $arAllFields[$fieldName] = $obField;
                }
            }
        }
        
        return $arAllFields;
    }
    
    static public function getDisplayListFields(Entity $obEntity, $userID = 0){
        $type               = "list";
        $arDisplayFields    = array();
        $arFields           = array();
        
        foreach($obEntity->getFields() AS $obField){
            $arFields[$obField->getFieldName()] = $obField;
        }
        
        foreach($obEntity->getCustomFields() AS $obCustomField){
            $arFields[$obCustomField->getFieldName()] = $obCustomField;
        }

        $arTmpEntityDisplay = static::builder()->where("entity_id", $obEntity->getTableName())
                                               ->whereIn("user_id", array(0, $userID))
                                               ->orderby("user_id", "DESC")
                                               ->fetchAll();
        
        $arEntityDisplay = array();
        
        foreach($arTmpEntityDisplay AS $arTmpEntityDisplayItem){
            $arDisplayData = CJSON::decode($arTmpEntityDisplayItem["data"], true);
            
            if(is_array($arDisplayData[$type]) && count($arDisplayData[$type])){
                $arEntityDisplay = $arDisplayData[$type];
                break;
            }
        }
        
        if(count($arEntityDisplay)){
            $fieldPk        = ExtraField::getPk();
            $arExtraFields  = CArrayHelper::index($obEntity->getExtraFields(), $fieldPk);
            
            foreach($arEntityDisplay AS $arDisplayField){
                $fieldName = $arDisplayField["field"];
                
                if(isset($arFields[$fieldName])){
                    $obField = $arFields[$fieldName];
                    
                    if($obField && $obField->isVisible()){ //if visible
                        $arDisplayFields[$fieldName] = $obField;                                                                                                
                    }
                }else{
                    $fieldID = ExtraField::getFieldIdByName($arDisplayField["field"]);
                    
                    if($fieldID && isset($arExtraFields[$fieldID])){
                        $arField = $arExtraFields[$fieldID];
                        
                        $fieldType = $arField["type"];
                        
                        if(class_exists($fieldType)){
                            $obField = new $fieldType($fieldName, $arField, $obEntity);
                            
                            if($obField && $obField->isVisible()){ //if visible
                                $arDisplayFields[$fieldName] = $obField;
                            }
                        }
                    }
                }
            }
        }else{
            foreach($arFields AS $fieldName => $obField){
                if($obField && $obField->isVisible()){ //if visible
                    $arDisplayFields[$fieldName] = $obField;                                                                                                
                }
            }
        }
        
        return $arDisplayFields;
    }
    
    static public function getDisplayFilterFields(Entity $obEntity, $userID = 0){        
        $type               = "filter";
        $arDisplayFields    = array();
        $arFields           = array();
        
        foreach($obEntity->getFields() AS $obField){
            $arFields[$obField->getFieldName()] = $obField;
        }
        
        foreach($obEntity->getCustomFields() AS $obCustomField){
            $arFields[$obCustomField->getFieldName()] = $obCustomField;
        }
        
        $arTmpEntityDisplay = static::builder()->where("entity_id", $obEntity->getTableName())
                                               ->whereIn("user_id", array(0, $userID))
                                               ->orderby("user_id", "DESC")
                                               ->fetchAll();
        
        $arEntityDisplay = array();
        
        foreach($arTmpEntityDisplay AS $arTmpEntityDisplayItem){
            $arDisplayData = CJSON::decode($arTmpEntityDisplayItem["data"], true);
            
            if(is_array($arDisplayData[$type]) && count($arDisplayData[$type])){
                $arEntityDisplay = $arDisplayData[$type];
                break;
            }
        }
        
        if(count($arEntityDisplay)){
            $fieldPk        = ExtraField::getPk();
            $arExtraFields  = CArrayHelper::index($obEntity->getExtraFields(), $fieldPk);
            
            foreach($arEntityDisplay AS $arDisplayField){
                $fieldName = $arDisplayField["field"];
                
                if(isset($arFields[$fieldName])){
                    $obField = $arFields[$fieldName];
                    
                    if($obField && $obField->isVisible()){ //if visible
                        $arDisplayFields[$fieldName] = $obField;                                                                                                
                    }
                }else{
                    $fieldID = ExtraField::getFieldIdByName($arDisplayField["field"]);
                    
                    if($fieldID && isset($arExtraFields[$fieldID])){
                        $arField = $arExtraFields[$fieldID];
                        
                        $fieldType = $arField["type"];
                        
                        if(class_exists($fieldType)){
                            $obField = new $fieldType($fieldName, $arField, $obEntity);
                            
                            if($obField && $obField->isVisible()){ //if visible
                                $arDisplayFields[$fieldName] = $obField;
                            }
                        }
                    }
                }
            }
        }else{
            foreach($arFields AS $fieldName => $obField){
                if($obField && $obField->isVisible()){ //if visible
                    $arDisplayFields[$fieldName] = $obField;                                                                                                
                }
            }
        }
        
        return $arDisplayFields;
    }
    
    static public function getDisplayDetailFields(Entity $obEntity, $userID = 0){
        $type = "detail";

        $arDisplayFields = array();
        
        $arFields = array();
        
        foreach($obEntity->getFields() AS $obField){
            $arFields[$obField->getFieldName()] = $obField;
        }
        
        foreach($obEntity->getCustomFields() AS $obCustomField){
            $arFields[$obCustomField->getFieldName()] = $obCustomField;
        }
        
        $arTmpEntityDisplay = static::builder()->where("entity_id", $obEntity->getTableName())
                                               ->whereIn("user_id", array(0, $userID))
                                               ->orderby("user_id", "DESC")
                                               ->fetchAll();
        
        $arEntityDisplay = array();
        
        foreach($arTmpEntityDisplay AS $arTmpEntityDisplayItem){
            $arDisplayData = CJSON::decode($arTmpEntityDisplayItem["data"], true);
            
            if(is_array($arDisplayData[$type]) && count($arDisplayData[$type])){
                $arEntityDisplay = $arDisplayData[$type];
                break;
            }
        }
        
        if(count($arEntityDisplay)){
            $fieldPk        = ExtraField::getPk();
            $arExtraFields  = CArrayHelper::index($obEntity->getExtraFields(), $fieldPk);
            
            foreach($arEntityDisplay AS $index => $arTab){
                $arTabFields = array();
                
                if(is_array($arTab["fields"])){
                    foreach($arTab["fields"] AS $arDisplayField){
                        $fieldName = $arDisplayField["field"];
                        
                        if(isset($arFields[$fieldName])){
                            $obField = $arFields[$fieldName];

                            if($obField && $obField->isVisible()){ //if visible
                                $arTabFields[$fieldName] = $obField;                                                                                                
                            }
                        }else{
                            $fieldID = ExtraField::getFieldIdByName($arDisplayField["field"]);
                            
                            if($fieldID && isset($arExtraFields[$fieldID])){
                                $arField = $arExtraFields[$fieldID];
                                
                                $fieldType = $arField["type"];
                                
                                if(class_exists($fieldType)){
                                    $obField = new $fieldType($fieldName, $arField, $obEntity);
                                    
                                    if($obField && $obField->isVisible()){ //if visible
                                        $arTabFields[$fieldName] = $obField;
                                    }
                                }
                            }
                        }
                    }
                }
                
                $arDisplayFields[] = array(
                    "name"      => "tab_" . ($index + 1),
                    "title"     => $arTab["title"],
                    "fields"    => $arTabFields
                );
            }
        }else{
            $arTabFields = array();
            
            foreach($arFields AS $fieldName => $obField){
                if($obField && $obField->isVisible()){ //if visible
                    $arTabFields[$fieldName] = $obField;                                                                                                
                }
            }
            
            $arDisplayFields[] = array(
                "name"      => "tab_main",
                "title"     => "Основное",
                "fields"    => $arTabFields
            );
        }

        return $arDisplayFields;
    }
    
    static public function setDisplayFields($obEntity, array $arData, $type, $userID = 0){
        $arTmpEntityDisplay = static::builder()->where("entity_id", $obEntity->getTableName())
                                               ->where("user_id", $userID)
                                               ->limit(1)
                                               ->fetch();
        
        if($arTmpEntityDisplay){
            $arEntityDisplay = CJSON::decode($arTmpEntityDisplay["data"], true);
            
            if(count($arData)){
                $arEntityDisplay[$type] = $arData;
            }else{
                unset($arEntityDisplay[$type]);
            }
            
            return static::builder()->where("entity_id", $obEntity->getTableName())
                                    ->where("user_id", $userID)
                                    ->update(array(
                                       "data" => CJSON::encode($arEntityDisplay)
                                    ));
        }else{
            $arEntityDisplay = array(
                $type => $arData
            );
            
            return static::builder()->where("entity_id", $obEntity->getTableName())
                                    ->where("user_id", $userID)
                                    ->add(array(
                                        "entity_id" => $obEntity->getTableName(),
                                        "user_id"   => $userID,
                                        "data"      => CJSON::encode($arEntityDisplay)
                                    ));
        }
    }
}
?>