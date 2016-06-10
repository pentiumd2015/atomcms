<?
namespace Entity\Field\Additional;

use \Entity\ExtraField;
use \Entity\ExtraFieldValue;

class StringField extends Field{
    protected $arInfo = array(
        "title" => "Строка"
    );
    
    public function getColumnName(){
        return "value_string";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\StringRenderer($this);
    }
}
?>