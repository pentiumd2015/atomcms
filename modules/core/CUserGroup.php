<?
use Entity\Field\Scalar\IntegerField;
use Entity\Field\Scalar\StringField;
use Entity\Field\Scalar\DateTimeField;

use Entity\Field\Error;

use Entity\Field\Validate\Unique AS ValidateUnique;
use Entity\Field\Validate\RegExp as ValidateRegExp;

use DB\Expr;

class CUserGroup extends Entity\Manager{
    protected static $tableName = "user_group";
    
    protected static $info = [
        "title" => "Группа пользователей"
    ];
    
    const CODE_ADMIN           = "ADMIN",
          CODE_UNAUTHORISED    = "UNAUTHORISED";
    
    protected static $events = array(
        "ADD"       => "USER.GROUP.ADD",
        "UPDATE"    => "USER.GROUP.UPDATE",
        "DELETE"    => "USER.GROUP.DELETE",
    );
    
    public function getFields(){
        return array(
            new IntegerField("id", [
                "title"     => "ID",
                "primary"   => true,
                "disabled"  => true
            ]),
            new StringField("title", [
                "title"     => "Название",
                "required"  => true,
            ]),
            new StringField("code", [
                "title"     => "Код",
                "required"  => true,
                "validate"  => function(){
                    return [
                        [$this, "validateCode"],
                        new ValidateRegExp("/^[a-zA-Z0-9-_]+$/si"),
                        new ValidateUnique(),
                    ];
                },
            ]),
            new DateTimeField("date_add", [
                "title"     => "Дата добавления",
                "disabled"  => true
            ]),
            new DateTimeField("date_update", [
                "title"     => "Дата обновления",
                "disabled"  => true
            ]),
        );
    }

    public function validateCode($value, $result, $field){
        $fieldName = $field->getName();

        if(!$result->isNewRecord()){
            $value = $result->getItemValue($fieldName);
        }

        if(!$this->isSystemGroup($value)){
            return true;
        }else{
            return new Error($fieldName, "Вы не можете редактировать системные группы", "system_group");
        }
    }
    
    public static function isSystemGroup($alias){
        return in_array($alias, [
            self::CODE_UNAUTHORISED,
            self::CODE_ADMIN
        ]);
    }

    public function onBeforeAdd($result){
        $result->setDataValues([
            "date_add"      => new Expr("NOW()"),
            "date_update"   => new Expr("NOW()")
        ]);

        return true;
    }

    public function onBeforeUpdate($result){
        $result->setDataValues([
            "date_update" => new Expr("NOW()")
        ]);

        return true;
    }
}