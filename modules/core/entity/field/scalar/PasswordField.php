<?
namespace Entity\Field\Scalar;

use Entity\Field\Renderer\PasswordRenderer;

class PasswordField extends StringField{
    public $algorithm;
    
    protected $info = [
        "title" => "Пароль"
    ];

    public function __construct($name, $params = []){
        parent::__construct($name, $params);

        $safeParams = [ //присваиваем только разрешенные параметры
            "algorithm"
        ];

        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function getRenderer(){
        return new PasswordRenderer($this);
    }
    
    protected function onSave($value, $result){
        if(is_callable($this->algorithm)){
            $hash = call_user_func_array($this->algorithm, [$value, $result, $this]);
p($value, $hash);
            if($value == $hash){
                $data = $result->getData();

                unset($data[$this->name]);

                $result->setData($data);
            }else{
                $result->setDataValues([$this->name => $hash]);
            }
        }
    }
    
    public function onBeforeAdd($value, $result){
        if(is_callable($this->algorithm)){
            $result->setDataValues([$this->name => call_user_func_array($this->algorithm, [$value, $result, $this])]);
        }
    }
    
    public function onBeforeUpdate($value, $result){
        if(is_callable($this->algorithm)){
            $itemData = $result->getItemData();

            if($value == $itemData[$this->name]){ //хеш совпадает с тем, что в бд
                $data = $result->getData();

                unset($data[$this->name]);

                $result->setData($data);
            }else{
                $result->setDataValues([$this->name => call_user_func_array($this->algorithm, [$value, $result, $this])]);
            }
        }
    }
}