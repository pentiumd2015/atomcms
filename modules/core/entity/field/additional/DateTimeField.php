<?
namespace Entity\Field\Additional;

use \Helpers\CDateTime;
use \DB\Expr;

class DateTimeField extends AdditionalField{
    protected $arInfo = array(
        "title" => "Дата/Время"
    );
    
    public function getColumnName(){
        return "value_date";
    }    
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\DateTimeRenderer($this);
    }
    
    public function validate($value, $arData){
        $arResult = parent::validate($value, $arData);

        if($arResult["success"]){
            if(!($value instanceof Expr || (is_string($value) && CDateTime::validate($value, "Y-m-d H:i:s")))){
                $arResult["success"]    = false;
                $arResult["error"]      = self::ERROR_INVALID;
            }
        }
        
        return $arResult;
    }
}
?>