<?
namespace Entity\Field\Additional;

use \Entity\Field\Error;

class BooleanField extends AdditionalField{
    protected $arInfo = array(
        "title" => "Да/Нет"
    );
    
    public function __construct($fieldName = NULL, array $arParams = array(), \Entity\Entity $obEntity){
        parent::__construct($fieldName, $arParams, $obEntity);
        
        if(!$this->values){
            $this->values = array(1 => "Да", 0 => "Нет");
        }
    }
    
    public function getColumnName(){
        return "value_num";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\BooleanRenderer($this);
    }
    
    public function validate($value, $arData){
        $arResult = array();
        
        if(!isset($this->values[$value])){
            $arResult["success"]    = false;
            $arResult["error"]      = Error::ERROR_INVALID;
            $arResult["validator"]  = Error::ERROR_INVALID;
        }else{
            $arResult = parent::validate($value, $arData);
        }
        
        return $arResult;
    }
}
?>