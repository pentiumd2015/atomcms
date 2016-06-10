<?
namespace Entities;

class EntityItem extends \DB\Manager{
    static protected $_table    = "entity_item";
    static protected $_pk       = "entity_item_id";
    
    const TYPE_ITEM     = 1,
          TYPE_SECTION  = 2;
    
    static public $arTypes = array(
        self::TYPE_SECTION => array(
            "title" => "Раздел"
        ),
        self::TYPE_ITEM => array(
            "title" => "Элемент"
        )
    );
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityItemID, $arData){
        return static::_save($arData, $entityItemID);
    }
    
    static public function deleteByPk($entityItemID){
        $result = parent::deleteByPk($entityItemID);
        
        \CEvent::trigger("ENTITY.ITEM.DELETE", array($entityItemID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityItemID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
    
        if(!$entityItemID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название элемента";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название элемента";
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
        
        if(!count($arErrors)){
            $arOriginalData = $arData;
            $arData = static::getSafeFields($arData, array(
                "title",
                "priority",
                "description",
                "active",
                "entity_id"
            ));
            
            if(isset($arData["active"])){
                $arData["active"] = ($arData["active"] == 1) ? 1 : 0 ;
            }
            
            if($entityItemID){
                $arData["date_update"]  = new \DB\Expr("NOW()");
                
                parent::updateByPk($entityItemID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.ITEM.UPDATE", array($entityItemID, $arData));
            }else{
                $arData["type"]     = static::TYPE_ITEM;
                $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
                
                $entityItemID = parent::add($arData);

                if($entityItemID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.ITEM.ADD", array($entityItemID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityItemID){
                $arReturn["id"] = $entityItemID;
                
                /*item sections*/
                if($arOriginalData["sections"]){
                    EntitySectionItem::setValues($entityItemID, $arOriginalData["sections"]);
                }
                /*item sections*/
                
                /*add fields*/
                if(count($arFieldValues)){
                    EntityItemFieldValue::setValues($entityItemID, $arFieldValues);
                }
                /*add fields*/
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>