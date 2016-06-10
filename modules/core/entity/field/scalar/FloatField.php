<?
namespace Entity\Field\Scalar;

use \Entity\Field\Renderer\NumericRenderer;

class FloatField extends IntegerField{
    protected $arInfo = [
        "title" => "Число с плавающей точкой"
    ];
    
    public function getRenderer(){
        return new NumericRenderer($this);
    }
}
?>