<?
namespace Entities;

use \Helpers\CJSON;

class Entity extends \DB\Manager{
    static protected $_table    = "entity";
    static protected $_pk       = "entity_id";
    
    const SIGNATURE_SECTIONS        = 1,
          SIGNATURE_SECTION         = 2,
          SIGNATURE_ADD_SECTION     = 3,
          SIGNATURE_EDIT_SECTION    = 4,
          SIGNATURE_DELETE_SECTION  = 5,
          SIGNATURE_ELEMENTS        = 6,
          SIGNATURE_ELEMENT         = 7,
          SIGNATURE_ADD_ELEMENT     = 8,
          SIGNATURE_EDIT_ELEMENT    = 9,
          SIGNATURE_DELETE_ELEMENT  = 10;
          
    static protected $arDefaultSignatures = array(
        self::SIGNATURE_SECTIONS        => array(
            "title" => "Разделы"
        ),
        self::SIGNATURE_SECTION         => array(
            "title" => "Раздел"
        ),
        self::SIGNATURE_ADD_SECTION     => array(
            "title" => "Добавить раздел"
        ),
        self::SIGNATURE_EDIT_SECTION    => array(
            "title" => "Изменить раздел"
        ),
        self::SIGNATURE_DELETE_SECTION  => array(
            "title" => "Удалить раздел"
        ),
        self::SIGNATURE_ELEMENTS           => array(
            "title" => "Элементы"
        ),
        self::SIGNATURE_ELEMENT            => array(
            "title" => "Элемент"
        ),
        self::SIGNATURE_ADD_ELEMENT        => array(
            "title" => "Добавить элемент"
        ),
        self::SIGNATURE_EDIT_ELEMENT       => array(
            "title" => "Изменить элемент"
        ),
        self::SIGNATURE_DELETE_ELEMENT     => array(
            "title" => "Удалить элемент"
        ),
    );
    
    static public function getDefaultSignatureList(){
        return self::$arDefaultSignatures;
    }
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityID, $arData){
        return static::_save($arData, $entityID);
    }
    
    static public function deleteByPk($entityID){
        $result = parent::deleteByPk($entityID);
        
        \CEvent::trigger("ENTITY.DELETE", array($entityID));
        
        foreach(EntityAdminDisplay::$arRelation AS $relation){
            \CParam::delete("admin_display_" . $relation . "_list:entity_" . $entityID, false);
        }
        
        return $result;
    }
    
    static protected function _save($arData, $entityID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
    
        if(!$entityID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название сущности";
            }
            
            if(empty($arData["entity_group_id"])){
                $arErrors["entity_group_id"][] = "Выберите группу сущностей";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название сущности";
            }
            
            if(isset($arData["entity_group_id"]) && empty($arData["entity_group_id"])){
                $arErrors["entity_group_id"][] = "Выберите группу сущностей";
            }
        }
        
        $arData["priority"] = (int)$arData["priority"];
    
        if(!count($arErrors)){
            $arData = static::getSafeFields($arData, array(
                "title",
                "priority",
                "use_sections",
                "entity_group_id",
                "params"
            ));
            
            if(isset($arData["use_sections"])){
                $arData["use_sections"] = $arData["use_sections"] == 1 ? 1 : 0 ;
            }
            
            if($entityID){
                $arData["date_update"] = new \DB\Expr("NOW()");
                
                parent::updateByPk($entityID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.UPDATE", array($entityID, $arData));
            }else{
                $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
                
                $arData["params"]   = CJSON::encode(array(
                    "signatures" => Entity::getDefaultSignatureList()
                ));
                
                $entityID = parent::add($arData);
                
                if($entityID){
                    /*Add access*/
                    $arUserGroups = \Models\UserGroup::findAll();
                    
                    $arInsert = array();
                    
                    foreach($arUserGroups AS $obUserGroup){
                        $arInsert[] = array(
                            "user_group_id" => $obUserGroup->user_group_id,
                            "entity_id"     => $entityID,
                            "access"        => EntityAccess::DENIED
                        );
                    }
                    
                    EntityAccess::addMulti($arInsert);
                    /*Add access*/

                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.ADD", array($entityID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityID){
                $arReturn["id"] = $entityID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>