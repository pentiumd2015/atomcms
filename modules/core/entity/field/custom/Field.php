<?
namespace Entity\Field\Custom;

abstract class Field extends \Entity\Field\BaseField{
    public function getRenderer(){
        return new \Entity\Field\Renderer\StringRenderer($this);
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        
    }
    
    public function condition($method, array $args = []){
        $obBuilder = $this->getDispatcher()->getBuilder();
        
        if(method_exists($obBuilder, $method)){
            call_user_func_array([$obBuilder, $method], $args);
        }
    }
}