<?
namespace Entities;

class EntityAccess extends \DB\Manager{
    static protected $_table    = "entity_access";
    static protected $_pk       = "entity_access_id";
    
    const DENIED        = 0,        // 000000
          CAN_VIEW      = 1 << 0,   // 000001
          CAN_ADD       = 1 << 1,   // 000010
          CAN_EDIT      = 1 << 2,   // 000100
          CAN_DELETE    = 1 << 3,   // 001000
          CAN_ALL       = 1 << 4;   // 010000
    
    static protected $arAccessRules = array(
        self::DENIED    => array(
            "title" => "Нет доступа"
        ),
        self::CAN_VIEW  => array(
            "title" => "Просмотр"
        ),
        self::CAN_ADD  => array(
            "title" => "Добавление"
        ),
        self::CAN_EDIT  => array(
            "title" => "Изменение"
        ),
        self::CAN_ALL   => array(
            "title" => "Полный доступ"
        ),
    );
    
    static public function getAccessRules(){
        return self::$arAccessRules;
    }
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityAccessID, $arData){
        return static::_save($arData, $entityAccessID);
    }
    
    static public function deleteByPk($entityAccessID){
        $result = parent::deleteByPk($entityAccessID);
        
        \CEvent::trigger("ENTITY.ACCESS.DELETE", array($entityAccessID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityAccessID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$entityAccessID){ //add new
            if(empty($arData["user_group_id"])){
                $arErrors["user_group_id"][] = "Укажите id группы пользователей";
            }
            
            if(empty($arData["entity_id"])){
                $arErrors["entity_id"][] = "Поле должно быть привязано к сущности";
            }
        }else{
            if(isset($arData["user_group_id"]) && empty($arData["user_group_id"])){
                $arErrors["user_group_id"][] = "Укажите id группы пользователей";
            }
            
            if(isset($arData["entity_id"]) && empty($arData["entity_id"])){
                $arErrors["entity_id"][] = "Доступ должен быть привязан к сущности";
            }
        }

        if(!count($arErrors)){
            $arData = static::getSafeFields($arData, array(
                "user_group_id",
                "entity_id",
                "access"
            ));

            if($entityAccessID){
                parent::updateByPk($entityAccessID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.ACCESS.UPDATE", array($entityAccessID, $arData));
            }else{
                $entityAccessID = parent::add($arData);
                
                if($entityAccessID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.ACCESS.ADD", array($entityAccessID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityAccessID){
                $arReturn["id"] = $entityAccessID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
    
    static public function userCan($userID, $entityID, $accessRule){
        $obAccess = static::find(
            array(
                "select"    => "t1.access",
                "alias"     => "t1",
                "join"      => "INNER JOIN user_group_value t2 ON(t2.user_group_id=t1.user_group_id)",
                "condition" => "t2.user_id=? AND t1.entity_id=? AND t1.access>=?"
            ),
            array($userID, $entityID, $accessRule)
        );
        
        return $obAccess ? true : false ;
    }
    //p(\Entities\EntityAccess::userCan($userID, $entityID, \Entities\EntityAccess::CAN_VIEW));
}
?>