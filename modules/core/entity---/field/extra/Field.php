<?
namespace Entity\Field\Extra;

abstract class Field extends \Entity\Field\Field{
    abstract public function getColumnForValue();
    
    abstract public function prepareFetch(array $arData = array(), $primaryKey = false);
    
    public function isCustomField(){
        return false;
    }
}
?>