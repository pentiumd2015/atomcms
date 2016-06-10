<?
namespace Entity\Field\Scalar;

use \CDateTime;
use \DB\Expr;
use \Entity\Result\SelectResult;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\BaseResult;
use \Entity\Field\Renderer\DateTimeRenderer;

class DateTimeField extends Field{
    protected $arInfo = [
        "title" => "Дата/Время"
    ];
    
    public function getRenderer(){
        return new DateTimeRenderer($this);
    }
    
    public function validate($value, BaseResult $obResult){
        $validate = parent::validate($value, $obResult);
        
        if($validate === true){
            if(strlen($value) && !($value instanceof Expr || (is_scalar($value) && CDateTime::validate($value, "Y-m-d H:i:s")))){            
                $validate = new Error($this->name, "Неверный формат даты", Error::ERROR_INVALID);
            }
        }
        
        return $validate;
    }
    
    public function onSelect(){
        return new Expr("DATE_FORMAT({{table}}." . $this->name . ",'%d.%m.%Y %H:%i:%s')");
    }
    
    public function onBeforeAdd($value, AddResult $obResult){
        $this->onSave($value, $obResult);
    }
    
    public function onBeforeUpdate($value, UpdateResult $obResult){
        $this->onSave($value, $obResult);
    }
    
    protected function onSave($value, $obResult){
        if($value){
            $obResult->setDataValues([
                $this->name => (new CDateTime($value))->format("Y-m-d H:i:s")
            ]);
        }
    }
}
?>