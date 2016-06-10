<?
namespace Entity\Field\Validate;

use \Entity\Field\Error;

class Unique implements IValidate{
    public function validate($value, $pk, $arData, $obField){
        $fieldName  = $obField->getFieldName();
        $arItems    = $obField->getEntity()
                              ->builder()
                              ->where($fieldName, $value)
                              ->limit(2)
                              ->fetchAll();
        
        foreach($arItems AS $arItem){
            if($arItem[$pk] != $arData[$pk]){
                return new Error($fieldName, "Запись с таким значением уже существует [ID: " . $arItem[$pk] . "]", "not_unique");
            }
        }
        
        return true;
    }
}
?>