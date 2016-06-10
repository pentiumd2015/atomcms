<?
namespace Entity\Field\Additional;

class FloatField extends AdditionalField{    
    protected $arInfo = array(
        "title" => "Число с плавающей точкой"
    );
    
    public function getColumnName(){
        return "value_num";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\NumericRenderer($this);
    }
}
?>