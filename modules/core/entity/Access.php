<?
namespace Entity;

class Access extends Manager{
    static protected $_table    = "new_entity_access";
    static protected $_pk       = "id";
    
    const DENIED        = 0,        // 000000
          CAN_VIEW      = 1 << 0,   // 000001
          CAN_ADD       = 1 << 1,   // 000010
          CAN_EDIT      = 1 << 2,   // 000100
          CAN_DELETE    = 1 << 3,   // 001000
          CAN_ALL       = 1 << 4;   // 010000
          
    static protected $arEvents = array(
        "ADD"       => "ENTITY.ACCESS.ADD",
        "UPDATE"    => "ENTITY.ACCESS.UPDATE",
        "DELETE"    => "ENTITY.ACCESS.DELETE",
    );
    
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
    
    static public function getFields(){
        return array(
            new Field\IntegerField("id", array(
                "title"     => "ID",
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\StringField("entity_id", array(
                "title"     => "Entity ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\IntegerField("user_group_id", array(
                "title"     => "User Group ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\StringField("access", array(
                "title"     => "Access",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            )),
        );
    }
    
    static public function getAccessRules(){
        return self::$arAccessRules;
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
}
?>