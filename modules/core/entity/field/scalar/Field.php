<?
namespace Entity\Field\Scalar;

use \Entity\Field\BaseField;

abstract class Field extends BaseField{
    public function condition($method, array $arArgs = []){
        $this->getDispatcher()
             ->getBuilder()
             ->operation($method, $arArgs);
    }
    
    public function orderBy($by){
        $this->getDispatcher()
             ->getBuilder()
             ->operation(__FUNCTION__, [$this->name, $by]);
    }
    
    public function groupBy(){
        $this->getDispatcher()
             ->getBuilder()
             ->operation(__FUNCTION__, [$this->name]);
    }
    
    public function filter($value){
        $this->getDispatcher()
             ->getBuilder()
             ->where($this->name, $value);
    }
}