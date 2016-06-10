<?
namespace Entity\Field;

class BooleanField extends Field{
    protected $arInfo = array(
        "title" => "Да/Нет"
    );
    
    public function __construct($fieldName = NULL, array $arParams = array(), \Entity\Entity $obEntity){
        if(!isset($arParams["values"])){
            $arParams["values"] = array(1 => "Да", 0 => "Нет");
        }
        
        parent::__construct($fieldName, $arParams, $obEntity);
    }
    
    public function getRenderer(){
        return new Renderer\BooleanRenderer($this);
    }
    
    public function loadValues(){
        /*load at once*/
        static $arValues = NULL;
        
        if($arValues == NULL){
            $arParams = $this->getParams();
            
            if(is_callable($arParams["values"])){
                $valuesHandler = $arParams["values"];
                
                $arValues = $valuesHandler();
                $arValues = CArrayHelper::index($arValues, "id");
            }else if(is_array($arParams["values"])){
                $arValues = $arParams["values"];
            }
        }else{
            static $arValues = array();
        }
        /*load at once*/
        
        return $arValues;
    }
    
    public function validate($value, $arData){
        $validate = parent::validate($value, $arData);
        
        $arParams = $this->getParams();
        
        if($validate === true && strlen($value) && !isset($arParams["values"][$value])){
            $validate = new Error($this->getFieldName(), "Неверное значение", Error::ERROR_INVALID);
        }
        
        return $validate;
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        $obBuilder->orderBy($table . "." . $this->getFieldName(), $by);
    }
        
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->where($table . "." . $this->getFieldName(), $value);
        }
    }
}
?>