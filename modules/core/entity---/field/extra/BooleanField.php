<?
namespace Entity\Field\Extra;

use \Entity\Field\Error;

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
    
    public function getColumnForValue(){
        return "value_num";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\BooleanRenderer($this);
    }
    
    public function prepareFetch(array $arData = array(), $primaryKey = false){
        return $arData;
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
        $arResult = array();
        
        if(!isset($this->arParams["values"][$value])){
            $arResult["success"]    = false;
            $arResult["error"]      = Error::ERROR_INVALID;
            $arResult["validator"]  = Error::ERROR_INVALID;
        }else{
            $arResult = parent::validate($value, $arData);
        }
        
        return $arResult;
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