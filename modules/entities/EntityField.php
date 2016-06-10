<?
namespace Entities;

class EntityField extends \DB\Manager{
    static protected $_table    = "entity_field";
    static protected $_pk       = "entity_field_id";
    
    const FIELD_RELATION_ITEM       = 1,
          FIELD_RELATION_SECTION    = 2;
          
    const FIELD_TYPE_STRING         = 1,
          FIELD_TYPE_TEXT           = 2,
          FIELD_TYPE_LIST           = 3;
          
    static protected $arTypes = array(
        self::FIELD_TYPE_STRING    => "\Entities\EntityFieldTypes\FieldTypeString",
        self::FIELD_TYPE_TEXT      => "\Entities\EntityFieldTypes\FieldTypeText",
        self::FIELD_TYPE_LIST      => "\Entities\EntityFieldTypes\FieldTypeList",
    );
          
    static public $arRelation = array(
        self::FIELD_RELATION_ITEM       => array(
            "title" => "Элемент"
        ),
        self::FIELD_RELATION_SECTION    => array(
            "title" => "Раздел"
        ),
    );
    
    static public function getTypes(){
        return static::$arTypes;
    }
    
    static public function getType($obEntityField){
        $fieldType = $obEntityField->type;
        
        if(static::$arTypes[$fieldType]){
            $fieldTypeClass = static::$arTypes[$fieldType];
            
            return new $fieldTypeClass($obEntityField);
        }
        
        return false;
    }
    /*
    static public function getType($fieldType){
        if(static::$arTypes[$fieldType]){
            return static::$arTypes[$fieldType];
        }
        
        return false;
    }
    */
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityFieldID, $arData){
        return static::_save($arData, $entityFieldID);
    }
    
    static public function deleteByPk($entityFieldID){
        $result = parent::deleteByPk($entityFieldID);
        
        \CEvent::trigger("ENTITY.FIELD.DELETE", array($entityFieldID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityFieldID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$entityFieldID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название поля";
            }
            
            if(empty($arData["type"])){
                $arErrors["type"][] = "Выберите тип поля";
            }
            
            if(empty($arData["entity_id"])){
                $arErrors["entity_id"][] = "Поле должно быть привязано к сущности";
            }
            
            if(empty($arData["relation"])){
                $arErrors["relation"][] = "Выберите раздел или элемент для поля";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название поля";
            }
            
            if(isset($arData["type"]) && empty($arData["type"])){
                $arErrors["type"][] = "Выберите тип поля";
            }
            
            if(isset($arData["entity_id"]) && empty($arData["entity_id"])){
                $arErrors["entity_id"][] = "Поле должно быть привязано к сущности";
            }
            
            if(isset($arData["relation"]) && empty($arData["relation"])){
                $arErrors["relation"][] = "Выберите раздел или элемент для поля";
            }
        }
        
        $obEntityField = new \stdClass;
        $obEntityField->type = $arData["type"];
        
        $obFieldType = static::getType($obEntityField);
        
        if(!$obFieldType){
            $arErrors["type"][] = "Не найден тип поля";
        }
        
        $arData["priority"] = (int)$arData["priority"];
        
        if($arData["params"] && is_array($arData["params"])){
            $arData["params"] = \Helpers\CJSON::encode($arData["params"]);
        }
        
        if(!count($arErrors)){
            $arOriginalData = $arData;
            $arData = static::getSafeFields($arData, array(
                "title",
                "priority",
                "is_required",
                "is_unique",
                "is_multi",
                "type",
                "relation",
                "params",
                "entity_id",
                "description"
            ));
            
            if(isset($arData["is_required"])){
                $arData["is_required"]  = $arData["is_required"] == 1 ? 1 : 0 ;
            }
            
            if(isset($arData["is_unique"])){
                $arData["is_unique"]    = $arData["is_unique"] == 1 ? 1 : 0 ;
            }
            
            if(isset($arData["is_multi"])){
                $arData["is_multi"]     = $arData["is_multi"] == 1 ? 1 : 0 ;
            }
            
            $arFieldTypeList        = static::getTypes();
            $arData["relation"]     = isset(static::$arRelation[$arData["relation"]]) ? $arData["relation"] : key(static::$arRelation) ;
            $arData["type"]         = isset($arFieldTypeList[$arData["type"]]) ? $arData["type"] : key($arFieldTypeList) ;

            if($entityFieldID){
                $arData["date_update"] = new \DB\Expr("NOW()");
                
                parent::updateByPk($entityFieldID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.FIELD.UPDATE", array($entityFieldID, $arData));
            }else{
                $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
                
                $entityFieldID = parent::add($arData);
                
                if($entityFieldID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.FIELD.ADD", array($entityFieldID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityFieldID){
                if($arOriginalData["variants"]){
                    EntityFieldVariant::setValues($entityFieldID, $arOriginalData["variants"]);
                }
                
                $arReturn["id"] = $entityFieldID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>