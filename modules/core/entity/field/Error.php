<?
namespace Entity\Field;

class Error{
    protected $code;
    protected $fieldName;
    protected $message;
    
    const ERROR_REQUIRED    = "required",
          ERROR_INVALID     = "invalid";
    
    public function __construct($fieldName, $message, $code = NULL){
        $this->message      = $message;
        $this->fieldName    = $fieldName;
        $this->code         = $code;
    }
    
    public function getCode(){
        return $this->code;
    }
    
    public function getMessage(){
        return $this->message;
    }
    
    public function getFieldName(){
        return $this->fieldName;
    }
}
?>