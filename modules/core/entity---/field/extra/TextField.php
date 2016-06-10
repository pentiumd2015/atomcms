<?
namespace Entity\Field\Extra;

class TextField extends Field{
    protected $arInfo = array(
        "title" => "Текст"
    );
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\TextRenderer($this);
    }
    
    public function prepareFetch(array $arData = array(), $primaryKey = false){
        return $arData;
    }
    
    public function getColumnForValue(){
        return "value_text";
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        
    }
}
?>