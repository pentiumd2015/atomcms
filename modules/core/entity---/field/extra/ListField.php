<?
namespace Entity\Field\Extra;

use \Helpers\CArrayHelper;
use \Helpers\CBuffer;

class ListField extends Field{
    protected $arInfo = array(
        "title" => "Список значений"
    );
    
    public function getColumnForValue(){
        return "value_enum";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\ListRenderer($this);
    }
    
    public function prepareFetch(array $arData = array(), $primaryKey = false){
        return $arData;
    }
    
    public function loadValues(){
        /*load at once*/
        static $arValues = NULL;
        
        if($arValues == NULL){
            $arFieldParams = $this->getParams();
            
            if(is_callable($arFieldParams["values"])){
                $valuesHandler = $arFieldParams["values"];
                
                $arValues = $valuesHandler();
                $arValues = CArrayHelper::index($arValues, "id");
            }else if(is_array($arFieldParams["values"])){
                $arValues = $arFieldParams["values"];
            }else{
                $arValues = array(1 => "some");
                /*
                $arFieldVariants = EntityFieldVariant::findAll(array(
                    "condition" => "entity_field_id=?",
                    "order"     => "priority ASC"
                ), array($this->obEntityField->entity_field_id));*/
            }
        }
        /*load at once*/
        
        return $arValues;
    }
        
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->where($table . "." . $this->getFieldName(), $value);
        }
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        $obBuilder->orderBy($table . "." . $this->getFieldName(), $by);
    }
}
?>