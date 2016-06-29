<?
namespace Entity\Field\Scalar;

use Entity\Field\Renderer\TextRenderer;

class TextField extends ScalarField{
    protected $info = [
        "title" => "Текст"
    ];
    
    public function getRenderer(){
        return new TextRenderer($this);
    }
    
    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $this->getDispatcher()
                 ->getQuery()
                 ->where($this->name, "LIKE", "%" . $value . "%");
        }
    }
}