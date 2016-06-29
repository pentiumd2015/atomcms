<?
namespace Entity\Field\Additional;

use Entity\Field\Renderer\StringRenderer;

class StringField extends Field{
    protected $info = [
        "title" => "Строка"
    ];
    
    public function getColumnName(){
        return "value_string";
    }
    
    public function getRenderer(){
        return new StringRenderer($this);
    }
}