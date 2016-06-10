<?
use \Entity\Field\Scalar\IntegerField;
use \Entity\Field\Scalar\StringField;
use \Entity\Field\Scalar\DateTimeField;

use \Entity\Field\Custom\UserGroupAccessField;

use \Entity\Field\Error;

use \Entity\Field\Validate\Unique AS ValidateUnique;
use \Entity\Field\Validate\RegExp as ValidateRegExp;

use \DB\Expr;

class CUserGroup extends \Entity\Entity{
    static protected $_table    = "user_group";
    static protected $_pk       = "id";
    
    static protected $arInfo = array(
        "title" => "Группа пользователей"
    );
    
    const ALIAS_ADMIN           = "ADMIN",
          ALIAS_UNAUTHORISED    = "UNAUTHORISED";
    
    static protected $arEvents = array(
        "ADD"       => "USER.GROUP.ADD",
        "UPDATE"    => "USER.GROUP.UPDATE",
        "DELETE"    => "USER.GROUP.DELETE",
    );
    
    public function getFields(){
        return array(
            new IntegerField("id", array(
                "title"     => "ID",
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new StringField("title", array(
                "title"     => "Название",
                "required"  => true,
                "visible"   => true,
            ), $this),
            new StringField("alias", array(
                "title"     => "Алиас",
                "required"  => true,
                "visible"   => true,
                "validate"  => function(){
                    return array(
                        new ValidateRegExp("/^[a-zA-Z0-9-_]+$/si"),
                        function($value, $pk, $arData, $obField){
                            if(!$obField->getEntity()->isSystemGroup($arData["alias"])){
                                return true;
                            }else{
                                return new Error($obField->getFieldName(), "Вы не можете редактировать системные группы", "system_group");
                            }
                        },
                        new ValidateUnique(),
                    );
                },
            ), $this),
            new DateTimeField("date_add", array(
                "title"     => "Дата добавления",
                "visible"   => true, //L - List, E - Edit, A - Add, F - Filter
                "disabled"  => true
            ), $this),
            new DateTimeField("date_update", array(
                "title"     => "Дата изменения",
                "visible"   => true,
                "disabled"  => true
            ), $this)
        );
    }
    
    static public function isSystemGroup($alias){
        return in_array($alias, array(
            self::ALIAS_UNAUTHORISED, 
            self::ALIAS_ADMIN
        ));
    }
    
    public function getCustomFields(){
        return array(
            new UserGroupAccessField("access", array(
                "title"     => "Уровень доступа",
                "visible"   => true,
                "multi"     => true
            ), $this)
        );
    }
    
    public function onBeforeAdd($obResult){
        $obResult->setDataValues([
            "date_add"      => new Expr("NOW()"),
            "date_update"   => new Expr("NOW()")
        ]);
        
        return true;
    }
    
    public function onBeforeUpdate($obResult, $id){
        $obResult->setDataValues([
            "date_update" => new Expr("NOW()")
        ]);

        return true;
    }
}
?>