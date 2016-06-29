<?
namespace DB\Manager\Validate;

use DB\Manager\Error;

class Length implements IValidate{
    protected $min;
	protected $max;
    
    public function __construct($min = 1, $max = null){
		if($min !== null){
			$this->min = (int)$min;
		}

		if($max !== null){
			$this->max = (int)$max;
		}
	}
    
    public function validate($value, $column, $result, $manager){
        if($this->min !== null && strlen($value) < $this->min){
			return new Error($column, "Длина должна быть не менее " . $this->min, Error::ERROR_INVALID);
		}

		if($this->max !== null && strlen($value) > $this->max){
            return new Error($column, "Длина должна быть не более " . $this->max, Error::ERROR_INVALID);
		}
        
        return true;
    }
}