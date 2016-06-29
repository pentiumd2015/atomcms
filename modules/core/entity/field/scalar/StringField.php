<?
namespace Entity\Field\Scalar;

use Entity\Field\Renderer\StringRenderer;

class StringField extends Field{
    protected $info = [
        "title" => "Строка"
    ];
    
    public function getRenderer(){
        return new StringRenderer($this);
    }
    
    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $this->getDispatcher()
                 ->getQuery()
                 ->where($this->name, "LIKE", "%" . $value . "%");
        }
    }
}