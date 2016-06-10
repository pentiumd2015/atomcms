<?
class UserGroupValue extends \NewEntity\Manager{
    static protected $entityName    = "user_group_value";
    static protected $pk            = "user_group_value_id";
    
    static public function getBaseFields(){
        return array(
            new \NewEntity\FieldType\Base\IntegerField("user_group_value_id", array(
                "title"     => "ID",
                "primary"   => true
            )),
            new \NewEntity\FieldType\Base\IntegerField("user_group_id"),
            new \NewEntity\FieldType\Base\IntegerField("user_id"),
            new \NewEntity\FieldType\Base\RelationField("UserGroup", array(
                "entity"    => "UserGroup",
                "ref"           => array("this.user_group_id" => "ref.user_group_id")
            )),
            new \NewEntity\FieldType\Base\RelationField("User", array(
                "entity"    => "\NewEntity\UserEntity",
                "ref"           => array("this.user_id" => "ref.user_id")
            ))
        );
    }
}

class UserGroup extends \NewEntity\Manager{
    static protected $entityName    = "user_group";
    static protected $pk            = "user_group_id";
    
    static public function getBaseFields(){
        return array(
            new \NewEntity\FieldType\Base\IntegerField("user_group_id", array(
                "title"     => "ID",
                "primary"   => true
            )),
            new \NewEntity\FieldType\Base\StringField("title"),
            new \NewEntity\FieldType\Base\StringField("alias"),
            new \NewEntity\FieldType\Base\RelationField("UserGroupValue", array(
                "entity"    => "UserGroupValue",
                "ref"           => array("this.user_group_id" => "ref.user_group_id")
            ))
        );
    }
}
?>