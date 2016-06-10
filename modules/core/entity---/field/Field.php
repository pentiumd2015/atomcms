<?
namespace Entity\Field;

use \DB\Expr;

abstract class Field{
    private $arParams = array();
    private $fieldName;
    private $obEntity;
    protected $arInfo = array();
    
    public function __construct($fieldName = NULL, array $arParams = array(), \Entity\Entity $obEntity){
        if($fieldName){
            $this->fieldName = $fieldName;
        }
        
        $this->arParams = $arParams;
        $this->obEntity = $obEntity;
    }
    
    public function setEntity(\Entity\Entity $obEntity){
        $this->obEntity = $obEntity;
    }
    
    public function getEntity(){
        return $this->obEntity;
    }
    
    public function getInfo(){
        return $this->arInfo;
    }
    
    public function setInfo(array $arInfo){
        $this->arInfo = $arInfo;
    } 
    
    abstract public function getRenderer();
    abstract public function orderBy($by, \Entity\Builder $obBuilder);
    abstract public function filter($value, \Entity\Builder $obBuilder);
    
    public function setParams($arParams){
        $this->arParams = $arParams;
        
        return $this;
    }
    
    public function getParams(){
        return $this->arParams;
    }
    
    public function getFieldName(){
        return $this->fieldName;
    }
    
    public function setFieldName($fieldName){
        $this->fieldName = $fieldName;
        
        return $this;
    }
    
    public function isVisible(){
        $arParams = $this->getParams();
        
        if($arParams["visible"]){
            return true;
        }
        
        return false;
    }
    
    public function validate($value, $arData){
        $fieldName = $this->getFieldName();
        
        $isValid = true;
        
        if($this->arParams["required"]){
            if(is_array($value)){
                $isValid = false;
                
                foreach($value AS $v){
                    if(strlen($v)){
                        $isValid = true;
                        break;
                    }
                }
            }else if(!$value instanceof Expr){
                $isValid = strlen($value) > 0;
            }
            
            if(!$isValid){
                return new Error($fieldName, "Поле является обязательным для заполнения", Error::ERROR_REQUIRED);
            }
        }

        if($isValid && is_callable($this->arParams["validate"])){
            $validate       = $this->arParams["validate"];
            $arValidators   = $validate();
            $pk             = $this->getEntity()->getPk();
            
            if(is_array($arValidators)){
                foreach($arValidators AS $key => $validator){
                    
                    if($validator instanceof Validate\IValidate){
                        $validateResult = $validator->validate($value, $pk, $arData, $this);
                        
                        if($validateResult !== true){
                            return $validateResult;
                        }
                    }else if(is_callable($validator)){
                        $validateResult = $validator($value, $pk, $arData, $this);
                        
                        if($validateResult !== true){
                            if(!$validateResult instanceof Error){
                                $error = is_integer($key) ? Error::ERROR_INVALID : $key;
                                $validateResult = new Error($fieldName, "Неверное значение поля", $error);
                            }
                            
                            return $validateResult;
                        }
                    }else if(is_string($validator) && !preg_match($validator, $value)){
                        $error = is_integer($key) ? Error::ERROR_INVALID : $key;
                        return new Error($fieldName, "Неверное значение поля", $error);
                    }
                }
            }
        }
        
        return $isValid;
    }
}
?>