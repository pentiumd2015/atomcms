<?
namespace NewEntity\FieldType\Base;

class BooleanField extends ScalarField{
    public function validate($arData){
        $arResult = parent::validate($arData);
        
        if($arResult["success"]){
            $value = $arData[$this->fieldName];
            
            if(!isset($this->arField["value"][$value])){
                $arResult["success"]    = false;
                $arResult["error"]      = "invalid";
            }
        }
        
        return $arResult;
    }
}
?>