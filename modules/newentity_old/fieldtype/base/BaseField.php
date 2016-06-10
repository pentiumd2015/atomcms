<?
namespace NewEntity\FieldType\Base;

class BaseField extends \NewEntity\FieldType\Field{
    protected $fieldName;
    protected $arParams = array();
    
    const ERROR_REQUIRED = "required",
          ERROR_INVALID  = "invalid";
    
    public function __construct($fieldName, $arParams = array()){
        $this->fieldName = $fieldName;
        
        if(is_array($this->arParams)){
            $this->arParams = $arParams;
        }
    }
    
    public function getFieldName(){
        return $this->fieldName;
    }
    
    public function setFieldName($fieldName){
        $this->fieldName = $fieldName;
        
        return $this;
    }
    
    public function setParams($arParams){
        $this->arParams = $arParams;
        
        return $this;
    }
    
    public function getParams(){
        return $this->arParams;
    }
    
    public function validate($arData){
        $error = false;
        
        if($this->arParams["required"] && !strlen($arData[$this->fieldName])){
            $error = self::ERROR_REQUIRED;
        }else if(isset($this->arParams["format"]) && !preg_match("#" . $this->arParams["format"] . "#", preg_quote($arData[$this->fieldName]))){
            $error = self::ERROR_INVALID;
        }
        
        $arResult = array();
        
        if($error){
            $arResult["success"]    = false;
            $arResult["error"]      = $error;
        }else{
            $arResult["success"]    = true;
        }
        
        return $arResult;
    }
}
?>