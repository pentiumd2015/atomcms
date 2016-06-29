<?
namespace Entity\Field;

use DB\Expr;
use DB\Manager\Error;
use DB\Manager\Validate\IValidate;
use DB\Manager\Validate\Required AS ValidateRequired;

abstract class BaseField{
    protected $name;
    protected $alias;
    protected $fieldDispatcher;
    protected $info = [];
    
    public $title           = "";
    public $description     = "";
    public $sortable        = true;
    public $filterable      = true;
    public $disabled        = false;
    public $required        = false;
    public $multi           = false;
    public $visible         = true;
    public $validate        = null;
    public $onSaveData      = null;
    public $onFetchData     = null;
    public $defaultValue    = null;
    
    abstract public function getRenderer();
    abstract public function condition($method, array $args = []);

    public function __construct($name, $params = []){
        $this->name = $name;
        
        $safeParams = [ //присваиваем только разрешенные параметры
            "title",
            "description",
            "sortable",
            "filterable",
            "disabled",
            "required",
            "multi",
            "visible",
            "validate",
            "onSaveData",
            "onFetchData",
            "defaultValue"
        ];
        
        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function getDefaultValue(){
        return $this->defaultValue;
    }
    
    public function onFetch($result){}
    public function onBeforeAdd($value, $result){}
    public function onAfterAdd($value, $result){}
    public function onBeforeUpdate($value, $result){}
    public function onAfterUpdate($value, $result){}
    public function onBeforeDelete($result){}
    public function onAfterDelete($result){}
    public function onSelect(){
        return $this->name;
    }
    
    public function getInfo(){
        return $this->info;
    }
    
    public function setInfo(array $info){
        $this->info = $info;
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
        return $this->fieldDispatcher;
    }
    
    public function setDispatcher(BaseFieldDispatcher $fieldDispatcher){
        $this->fieldDispatcher = $fieldDispatcher;
        
        return $this;
    }
    
    public function validate($value, $result){
        if($value instanceof Expr){
			return true;
		}

        $validators = [];

        if($this->required){
            $validators[] = new ValidateRequired;
        }

        $validate = $this->validate;

        if(is_callable($validate)){
            $validators = array_merge($validators, $validate());
        }

        if($this->disabled && $value !== null){
            return new Error($this->name, "Поле предназначено только для просмотра", Error::ERROR_INVALID);
        }

        $manager = $this->fieldDispatcher->getQuery()->getManager();

        foreach($validators AS $key => $validator){
            if($validator instanceof IValidate && (($validateError = $validator->validate($value, $this->name, $result, $manager)) !== true)){
                return $validateError;
            }else if(is_callable($validator) && (($validateError = $validator($value, $this->name, $result, $manager)) !== true)){
                if($validateError instanceof Error){
                    return $validateError;
                }else{
                    return new Error($this->name, (is_string($validateError) ? $validateError : "Неверное значение поля"), is_numeric($key) ? Error::ERROR_INVALID : $key) ;
                }
            }
        }

        return true;
    }
}