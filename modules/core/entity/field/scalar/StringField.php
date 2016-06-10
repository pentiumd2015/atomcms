<?
namespace Entity\Field\Scalar;

use \Entity\Field\Renderer\StringRenderer;

class StringField extends Field{
    protected $arInfo = [
        "title" => "Строка"
    ];
    
    public function getRenderer(){
        return new StringRenderer($this);
    }
    
    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $this->getDispatcher()
                 ->getBuilder()
                 ->where($this->name, "LIKE", "%" . $value . "%");
        }
    }
    /*
    public function onSelect(){
        return new \DB\Expr("DATE_FORMAT(" . $this->fieldName . ", '%d.%m.%Y')");
    }*/
}
?>