<?
namespace Entity\Field\Scalar;

use Entity\Field\Renderer\BooleanRenderer;
use DB\Manager\Error;

class BooleanField extends ListField{
    protected $info = [
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
    
    public function validate($value, $result){
        $validate = parent::validate($value, $result);
        
        if($validate === true){
            if(strlen($value) && !isset($this->values[$value])){
                $validate = new Error($this->name, "Неверное значение", Error::ERROR_INVALID);
            }
        }
        
        return $validate;
    }
}