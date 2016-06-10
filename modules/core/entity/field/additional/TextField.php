<?
namespace Entity\Field\Extra;

class TextField extends Field{
    protected $arInfo = array(
        "title" => "Текст"
    );
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\TextRenderer($this);
    }
    
    public function getColumnName(){
        return "value_text";
    }
}
?>