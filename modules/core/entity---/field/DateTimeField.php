<?
namespace Entity\Field;

use \CDateTime;
use \DB\Expr;

class DateTimeField extends Field{
    protected $arInfo = array(
        "title" => "Дата/Время"
    );
    
    public function getRenderer(){
        return new Renderer\DateTimeRenderer($this);
    }
    
    public function validate($value, $arData){
        $validate = parent::validate($value, $arData);

        if($validate === true && strlen($value) && !($value instanceof Expr || (is_string($value) && CDateTime::validate($value, "Y-m-d H:i:s")))){            
            $validate = new Error($this->getFieldName(), "Неверный формат даты", Error::ERROR_INVALID);
        }
         
        return $validate;
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        $obBuilder->orderBy($table . "." . $this->getFieldName(), $by);
    }
        
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->where($table . "." . $this->getFieldName(), $value);
        }
    }
}
?>