<?
namespace Entity\Field;

use \DB\Expr;
use \DB\Builder AS DbBuilder;
use \Entity\Result\BaseResult;
use \Entity\Result\SelectResult;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Entity;

abstract class BaseField{
    protected $name;
    protected $alias;
    protected $obFieldDispatcher;
    protected $arInfo = [];
    
    public $title       = "";
    public $description = "";
    public $disabled    = false;
    public $required    = false;
    public $multi       = false;
    public $visible     = true;
    public $primary     = false;
    public $validate    = null;
    public $onSaveData  = null;
    public $onFetchData = null;
    
    abstract public function getRenderer();
    abstract public function condition($method, array $args = []);

    public function __construct($name, array $arParams = []){
        $this->name = $name;
        
        foreach($arParams AS $name => $value){
            $this->{$name} = $value;
        }
    }
    
    public function onFetch(SelectResult $obResult){}
    public function onBeforeAdd($value, AddResult $obResult){}
    public function onAfterAdd($value, AddResult $obResult){}
    public function onBeforeUpdate($value, UpdateResult $obResult){}
    public function onAfterUpdate($value, UpdateResult $obResult){}
    public function onBeforeDelete($value, DeleteResult $obResult){}
    public function onAfterDelete($value, DeleteResult $obResult){}
    public function onSelect(){}
    
    public function getInfo(){
        return $this->arInfo;
    }
    
    public function setInfo(array $arInfo){
        $this->arInfo = $arInfo;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getAlias(){
        return $this->alias;
    }
    
    public function setAlias($alias){
        $this->alias = $alias;
        
        return $this;
    }
    
    public function getDispatcher(){
        return $this->obFieldDispatcher;
    }
    
    public function setDispatcher(BaseFieldDispatcher $obFieldDispatcher){
        $this->obFieldDispatcher = $obFieldDispatcher;
        
        return $this;
    }
    
    public function validate($value, BaseResult $obResult){
        $isValid = true;
        
        if($this->required){
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
                return new Error($this->name, "Поле является обязательным для заполнения", Error::ERROR_REQUIRED);
            }
        }

        if($isValid && is_callable($this->validate)){
            $validate       = $this->validate;
            $pk             = $this->getDispatcher()->getBuilder()->getEntity()->getPk();
            $arValidators   = $validate();
            
            if(is_array($arValidators)){
                foreach($arValidators AS $key => $validator){
                    if($validator instanceof Validate\IValidate){
                        if(($validateResult = $validator->validate($obResult, $this)) !== true){
                            return $validateResult;
                        }
                    }else if(is_callable($validator)){
                        if(($validateResult = $validator($obResult, $this)) !== true){
                            if(!$validateResult instanceof Error){
                                $error = is_numeric($key) ? Error::ERROR_INVALID : $key;
                                $validateResult = new Error($this->name, "Неверное значение поля", $error);
                            }
                            
                            return $validateResult;
                        }
                    }else if(is_string($validator) && !preg_match($validator, $value)){
                        $error = is_numeric($key) ? Error::ERROR_INVALID : $key;
                        return new Error($this->name, "Неверное значение поля", $error);
                    }
                }
            }
        }
        
        return $isValid;
    }
}
?>