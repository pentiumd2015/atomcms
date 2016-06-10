<?
namespace Entity\Field\Validate;

use \Entity\Field\Error;
use \DB\Builder;

class Unique implements IValidate{
    public function validate($obResult, $obField){
        $fieldName  = $obField->getName();
        $arData     = $obResult->getData();
        $obEntity   = $obField->getDispatcher()->getBuilder()->getEntity();
        $pk         = $obEntity->getPk();
        $value      = $arData[$fieldName];
        $arItems    = (new Builder)->from($obEntity->getTableName())
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