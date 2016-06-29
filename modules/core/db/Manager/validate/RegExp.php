<?
namespace DB\Manager\Validate;

use DB\Manager\Error;

class RegExp implements IValidate{
    protected $pattern;
    protected $errorMessage;
    
    public function __construct($pattern, $errorMessage = null){
		$this->pattern      = $pattern;
        $this->errorMessage = ($errorMessage === null) ? "Значение не соответствует маске " . $this->pattern : $errorMessage ;
	}
    
    public function validate($value, $column, $result, $manager){
        if(!preg_match($this->pattern, $value)){
            return new Error($column, $this->errorMessage, Error::ERROR_INVALID);
		}
        
        return true;
    }
}