<?
namespace Entity\Field\Validate;

use \Entity\Field\Error;

class RegExp implements IValidate{
    protected $pattern;
    
    public function __construct($pattern){
		$this->pattern = $pattern;
	}
    
    public function validate($value, $pk, $arData, $obField){
        $fieldName = $obField->getFieldName();
        
        if(!preg_match($this->pattern, $value)){
            return new Error($fieldName, "Значение не соответствует маске " . $this->pattern, Error::ERROR_INVALID);
		}
        
        return true;
    }
}
?>