<?
namespace DB\Manager;

class Error{
    protected $code;
    protected $columnName;
    protected $message;

    const ERROR_REQUIRED    = "required",
          ERROR_INVALID     = "invalid";

    public function __construct($columnName, $message, $code = null){
        $this->message      = $message;
        $this->columnName   = $columnName;
        $this->code         = $code;
    }

    public function getCode(){
        return $this->code;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getColumnName(){
        return $this->columnName;
    }
}