<?
namespace Entity\Field\Extra;

use \Entity\ExtraField;
use \Entity\ExtraFieldValue;

class StringField extends Field{
    protected $arInfo = array(
        "title" => "Строка"
    );
    
    public function getColumnForValue(){
        return "value_string";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\StringRenderer($this);
    }
    
    public function prepareFetch(array $arData = array(), $primaryKey = false){
        return $arData;
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $obBuilder->where($this->getFieldName(), "LIKE", "%" . $value . "%");
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $obBuilder->orderBy($this->getFieldName(), $by);
    }
}
?>