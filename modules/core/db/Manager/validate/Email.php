<?
namespace DB\Manager\Validate;

use DB\Manager\Error;

class Email implements IValidate{
    public function validate($value, $column, $result, $manager){
        if(strlen($value) && !preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $value)){
            return new Error($column, "Введите корректный E-mail", Error::ERROR_INVALID);
		}
        
        return true;
    }
}