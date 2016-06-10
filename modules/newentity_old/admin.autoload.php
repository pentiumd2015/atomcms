<?/*
$connection = \DB\Connection::getInstance();
set_time_limit(0);
ignore_user_abort(true);
for($i=200000;$i<400000;$i++){
    $connection->query("INSERT INTO `test_part_table_1` (title) VALUES(?)", array("title " . $i));
}*/
namespace NewEntity;


class CEntity extends Manager{
    static protected $entityName;
    
    static public function getBaseFields(){
        return array();
    }
    
    static public function getExtraFields(){
        
    }
}

include __DIR__ ."/s.php";

class UserEntity extends CEntityItem{
    static protected $entityName    = "user";
    static protected $pk            = "user_id";
    
    static public function getBaseFields(){
        return array(
            new FieldType\Base\IntegerField("user_id", array(
                "title"     => "ID",
                "primary"   => true
            )),
            new FieldType\Base\StringField("login", array(
                "title"     => "Логин",
                "required"  => true,
                "unique"    => true,
                "validate"  => array(
                    "/\d+/",
                    function($value){
                        return !empty($value);
                    }
                )
            )),
            new FieldType\Base\ExprField("someField", array(
                "title"         => "ID + 10",
                "expr"          => "%s + 10",
                "exprParams"    => array("user_id")
            )),
            new FieldType\Base\RelationField("UserGroupValue", array(
                "entity"        => "UserGroupValue",
                "ref"           => array("this.user_id" => "ref.user_id"),
                "joinType"      => "LEFT"
            ))
        );
    }
}





class CEntityFieldDisplay{
    
}

class CEntityAccess{
    
}

class CEntityFieldAccess{
    
}


//new \DB\Expr("NOW()")
p(UserEntity::getList(array(
    "select" => array("login", "someField", "user_id" => "id", "UserGroupValue.UserGroup.title" => "user_group_title", "UserGroupValue.UserGroup.alias" => "user_group_alias")
))->fetchAll());
?>