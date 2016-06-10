<?
namespace Entity\Field\Validate;

use \Entity\Field\Error;

class Length implements IValidate{
    protected $min;
	protected $max;
    
    public function __construct($min = 1, $max = NULL){
		if($min !== NULL){
			$this->min = (int)$min;
		}

		if($max !== NULL){
			$this->max = (int)$max;
		}
	}
    
    public function validate($value, $pk, $arData, $obField){
        $fieldName = $obField->getFieldName();
        
        if($this->min !== NULL && strlen($value) < $this->min){
			return new Error($fieldName, "Длина должна быть не менее " . $this->min, Error::ERROR_INVALID);
		}

		if($this->max !== NULL && strlen($value) > $this->max){
            return new Error($fieldName, "Длина должна быть не более " . $this->max, Error::ERROR_INVALID);
		}
        
        return true;
    }
}
?>