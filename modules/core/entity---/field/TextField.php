<?
namespace Entity\Field;

class TextField extends Field{
    protected $arInfo = array(
        "title" => "Текст"
    );
    
    public function getRenderer(){
        return new Renderer\TextRenderer($this);
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        $obBuilder->orderBy($table . "." . $this->getFieldName(), $by);
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->where($table . "." . $this->getFieldName(), "LIKE", "%" . $value . "%");
        }
    }
}
?>