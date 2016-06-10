<?
namespace Entity\Field\Validate;

use \Entity\Field\Error;

class Email implements IValidate{
    public function validate($value, $pk, $arData, $obField){
        $fieldName = $obField->getFieldName();
        
        if(strlen($value) && !preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $value)){
            return new Error($fieldName, "Введите корректный E-mail", Error::ERROR_INVALID);
		}
        
        return true;
    }
}
?>