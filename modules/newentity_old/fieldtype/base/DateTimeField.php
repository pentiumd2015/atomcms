<?
namespace NewEntity\FieldType\Base;

use \Helpers\CDateTime;

class DateTimeField extends ScalarField{
    public function validate($arData){
        $arResult = parent::validate($arData);

        if($arResult["success"]){
            $value = $arData[$this->fieldName];
            
            if(!($value instanceof \DB\Expr || (is_string($value) && CDateTime::validate($value, "Y-m-d H:i:s")))){
                $arResult["success"]    = false;
                $arResult["error"]      = self::ERROR_INVALID;
            }
        }
        
        return $arResult;
    }
}
?>