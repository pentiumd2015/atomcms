<?
namespace Entity\Field\Scalar;

use \Entity\Result\BaseResult;
use \Entity\Field\Renderer\BooleanRenderer;

class BooleanField extends Field{
    protected $arInfo = [
        "title" => "Да/Нет"
    ];
    
    public $values = [
        0 => [
            "title" => "Нет",
            "class" => "label-warning"
        ],
        1 => [
            "title" => "Да",
            "class" => "label-success"
        ],
    ];
    
    public function getRenderer(){
        return new BooleanRenderer($this);
    }
    
    public function validate($value, BaseResult $obResult){
        $validate = parent::validate($value, $obResult);
        
        if($validate === true){
            if(strlen($value) && !isset($this->values[$value])){
                $validate = new Error($this->name, "Неверное значение", Error::ERROR_INVALID);
            }
        }
        
        return $validate;
    }
}
?>