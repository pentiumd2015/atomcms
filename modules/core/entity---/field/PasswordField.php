<?
namespace Entity\Field;

class PasswordField extends StringField{
    protected $arInfo = array(
        "title" => "Пароль"
    );
    
    public function getRenderer(){
        return new Renderer\PasswordRenderer($this);
    }
    
    public function prepareSave($value, $pk, $arData){
        $arParams = $this->getParams();
        
        $callback = $arParams["algorithm"];
        
        if(is_callable($callback)){
            $hash = $callback($value);
            
            if($value == $hash){
                return false;
            }else{
                return $hash;
            }
        }else{
            return $value;
        }
    }
    
    public function getHash($value){
        return sha1($value);
    }
}
?>