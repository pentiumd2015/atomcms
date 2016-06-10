<?
namespace Entity\Field\Scalar;

use \Entity\Field\Renderer\TextRenderer;

class TextField extends ScalarField{
    protected $arInfo = [
        "title" => "Текст"
    ];
    
    public function getRenderer(){
        return new TextRenderer($this);
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_scalar($value) && strlen($value)){
            $obBuilder->where($table . "." . $this->name, "LIKE", "%" . $value . "%");
        }
    }
}
?>