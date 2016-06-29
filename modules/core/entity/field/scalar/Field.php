<?
namespace Entity\Field\Scalar;

use Entity\Field\BaseField;

abstract class Field extends BaseField{
    public $primary = false;
    
    public function __construct($name, $params = []){
        parent::__construct($name, $params);
        
        $safeParams = [ //присваиваем только разрешенные параметры
            "primary"
        ];
        
        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function condition($method, array $args = []){
        $this->getDispatcher()
             ->getQuery()
             ->internal($method, $args);
    }
    
    public function orderBy($by){
        $this->getDispatcher()
             ->getQuery()
             ->internal(__FUNCTION__, [$this->name, $by]);
    }
    
    public function groupBy(){
        $this->getDispatcher()
             ->getQuery()
             ->internal(__FUNCTION__, [$this->name]);
    }
    
    public function filter($value){
        $this->getDispatcher()
             ->getQuery()
             ->where($this->name, $value);
    }
}