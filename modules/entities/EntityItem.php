<?
namespace Entities;

class EntityItem extends \DB\Manager{
    static protected $_table    = "entity_item";
    static protected $_pk       = "entity_item_id";
    
    const EVENT_ADD     = "ADD",
          EVENT_UPDATE  = "UPDATE",
          EVENT_DELETE  = "DELETE";
    
    const TYPE_ELEMENT  = 1,
          TYPE_SECTION  = 2;
    
    static public $arTypes = array(
        self::TYPE_SECTION => array(
            "title" => "Раздел"
        ),
        self::TYPE_ELEMENT => array(
            "title" => "Элемент"
        )
    );
    
    static public function checkValues($entityItemID, $arData){
        $arErrors = array();
    
        if(!$entityItemID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
            
            if(empty($arData["type"])){
                $arErrors["type"][] = "Укажите тип";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
        }
        
        $arData["priority"] = (int)$arData["priority"];
        
        /*проверим данные доп полей.*/
        $arFieldValues = array();
        
        foreach($arData AS $field => $arValues){
            if(is_numeric($field)){
                $arFieldValues[(int)$field] = $arValues;
            }
        }
        
        if(count($arFieldValues)){
            $arFieldsCheckResult = EntityItemFieldValue::checkValues($arFieldValues);
            
            if(!$arFieldsCheckResult["success"]){
                $arErrors+= $arFieldsCheckResult["errors"];
            }
        }
        /*проверим данные доп полей.*/
        
        return $arErrors;
    }
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityItemID, $arData){
        return static::_save($arData, $entityItemID);
    }
    
    static public function deleteByPk($entityItemID){
        $result = parent::deleteByPk($entityItemID);
        
        \CEvent::trigger(static::EVENT_DELETE, array($entityItemID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityItemID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = static::checkValues($entityItemID, $arData);
        
        if(!count($arErrors)){
            $arOriginalData = $arData;
            
            if(isset($arData["active"])){
                $arData["active"] = ($arData["active"] == 1) ? 1 : 0 ;
            }
            
            $arData["date_update"] = new \DB\Expr("NOW()");
            
            if($entityItemID){
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "priority",
                    "description",
                    "active",
                    "entity_id",
                    "date_update"
                ));
                
                parent::updateByPk($entityItemID, $arData);
                
                \CEvent::trigger(static::EVENT_UPDATE, array($entityItemID, $arData));
            }else{
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "priority",
                    "description",
                    "active",
                    "entity_id",
                    "type"
                ));
                
                $arData["date_add"] = $arData["date_update"];
                
                $entityItemID       = parent::add($arData);

                if($entityItemID){
                    \CEvent::trigger(static::EVENT_ADD, array($entityItemID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityItemID){
                $arReturn["itemID"]     = $entityItemID;
                $arReturn["success"]    = true;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>