<?
namespace Entity\Field\Custom;

use Entity\Field\Renderer\StringRenderer;

abstract class Field extends \Entity\Field\BaseField{
    public function getRenderer(){
        return new StringRenderer($this);
    }
    
    public function onSelect(){
        return false;
    }
    
    public function filter($value){
        
    }
    
    public function condition($method, array $args = []){
        $query = $this->getDispatcher()->getQuery();
        
        if(method_exists($query, $method)){
            call_user_func_array([$query, $method], $args);
        }
    }
}