<?
namespace Entity\Field\Custom;

abstract class Field extends \Entity\Field\Field{
    abstract public function prepareFetch(array $arItems = array(), $primaryKey = false);
    
    public function isCustomField(){
        return true;
    }
    
    abstract public function add(array $arData, $primaryKey, array $arItems);
    abstract public function update(array $arData, $primaryKey, array $arItems);
    abstract public function delete($primaryKey, array $arItems);
}
?>