<?
namespace DB;

class Expr{
    protected $value;
    
    public function __construct($value){
        $this->value = $value;
    }
    
    public function getValue(){
        return $this->value;
    }
    
    public function setValue($value){
        $this->value = $value;
        
        return $this;
    }
}