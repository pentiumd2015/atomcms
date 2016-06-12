<?
namespace Entity\Field\Scalar;

use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Field\Renderer\PasswordRenderer;

class PasswordField extends StringField{
    public $algorithm;
    
    protected $arInfo = [
        "title" => "Пароль"
    ];
    
    public function getRenderer(){
        return new PasswordRenderer($this);
    }
    
    protected function onSave($value, $obResult){
        if(is_callable($this->algorithm)){
            $hash = call_user_func_array($this->algorithm, [$value]);
            
            return $value == $hash ? null : $hash ;
        }else{
            return $value;
        }
    }
    
    public function onBeforeAdd($value, AddResult $obResult){
        return $this->onSave($value, $obResult);
    }
    
    public function onBeforeUpdate($value, UpdateResult $obResult){
        return $this->onSave($value, $obResult);
    }
}
?>