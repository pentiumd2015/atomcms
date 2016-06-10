<?
namespace NewEntity\FieldType;

class Field implements IField{
    public function validate($arData){
        $arResult = array();
        
        $arResult["success"] = true;
        
        return $arResult;
    }
}
?>