<?
namespace Entity\Field;

class ListField extends Field{
    protected $arInfo = array(
        "title" => "Список значений"
    );
    
    public function getRenderer(){
        return new Renderer\ListRenderer($this);
    }
    
    public function prepareFetch($value, $pk, $arData){
        return $value;
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