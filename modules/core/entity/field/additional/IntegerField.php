<?
namespace Entity\Field\Additional;

class IntegerField extends AdditionalField{    
    protected $arInfo = array(
        "title" => "Целое число"
    );
    
    public function getColumnName(){
        return "value_num";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\NumericRenderer($this);
    }
}
?>