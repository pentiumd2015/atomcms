<?
namespace Entity\Field\Scalar;

use \Entity\Field\Renderer\ListRenderer;

class ListField extends Field{
    protected $arInfo = [
        "title" => "Список значений"
    ];
    
    public $values = [];
    
    public function getRenderer(){
        return new ListRenderer($this);
    }
}
?>