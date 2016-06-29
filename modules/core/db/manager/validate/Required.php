<?
namespace DB\Manager\Validate;

use DB\Manager\Error;

class Required implements IValidate{
    public function validate($value, $column, $result, $manager){
        if(is_array($value)){
            foreach($value AS $v){
                if(strlen($v)){
                    return true;
                }
            }

            return new Error($column, "Поле является обязательным для заполнения", Error::ERROR_REQUIRED);
        }else if(!strlen($value)){
            return new Error($column, "Поле является обязательным для заполнения", Error::ERROR_REQUIRED);
        }

        return true;
    }
}