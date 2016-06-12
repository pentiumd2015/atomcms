<?
namespace Entity;

use \DB\Expr;
use \DB\Builder AS DbBuilder;

abstract class Entity{
    static protected $_table;
    static protected $_entityName;
    
    static protected $arInfo = [];
    
    static protected $arEvents = [
        "ADD"       => "ENTITY.ADD",
        "UPDATE"    => "ENTITY.UPDATE",
        "DELETE"    => "ENTITY.DELETE",
    ];
    
    abstract public function getFields();
    
    static public function getClass(){
        return get_called_class();
    }
    
    static public function getEntityName(){
        return static::$_entityName ? static::$_entityName : static::getTableName() ;
    }
    
    static public function setEntityName($entityName){
        static::$_entityName = $entityName;
    }
    
    static public function builder(){
        return new Builder(new static);
    }
    
    static public function getInfo(){
        return static::$arInfo;
    }
    
    static public function setInfo(array $arInfo){
        static::$arInfo = $arInfo;
    }   
    
    static public function getTableName(){
        return static::$_table;
    }
    
    static public function setTableName($tableName){
        static::$_table = $tableName;
    }
    
    static public function getEventNames(){
        return static::$arEvents;
    }
    
    static public function setEventNames(array $arEvents){
        static::$arEvents = $arEvents;
    }
    
    static public function getPk(){
        if(static::$_pk === NULL){
            foreach(static::getFields() AS $obField){
                if(!$obField instanceof Field\Scalar\ScalarField){
                    continue;
                }
                
                $arParams = $obField->getParams();
                
                if(isset($arParams["isPrimaryKey"]) && $arParams["isPrimaryKey"]){
                    static::$_pk = $obField->getFieldName();
                    break;
                }
            }
            
            if(static::$_pk === NULL){
                static::$_pk = "id";
            }
        }
        
        return static::$_pk;
    }
    
    public function onBeforeAdd($obResult){
        return true;
    }
    
    public function onBeforeUpdate($obResult, $id){
        return true;
    }
    
    public function onBeforeDelete($obResult, $id){
        return true;
    }
    
    public function onAfterAdd($obResult, $id){}
    public function onAfterUpdate($obResult){}
    public function onAfterDelete($obResult){}
    
    static public function getByID($id){
        return static::builder()->where(static::getPk(), $id)->fetch();
    }
    
    static public function getAllByID($arIDs){
        return static::builder()->whereIn(static::getPk(), $arIDs)->fetchAll();
    }
    
    static public function add(array $arData){
        return static::builder()->add($arData);
    }
    
    static public function update($id, array $arData){
        return static::builder()->where(static::getPk(), $id)->update($arData);
    }
    
    static public function updateAll(array $arIDs, array $arData){
        return static::builder()->whereIn(static::getPk(), $arIDs)->update($arData);
    }
    
    static public function delete($id){
        return static::builder()->where(static::getPk(), $id)->delete();
    }
    
    static public function deleteAll(array $arIDs){
        return static::builder()->whereIn(static::getPk(), $arIDs)->delete();
    }
    
    public function search(array $arParams = []){
        $pk = $this->getPk();
        
        $obBuilder = $this->builder()->select($arParams["select"]);
        
        $arOrder = [$this->getPk() => "DESC"];
        
        /*Apply Request Sort*/
        if(isset($arParams["sort"]) && is_array($arParams["sort"])){
            $order  = key($arParams["sort"]);
            $by     = reset($arParams["sort"]);
            
            if($order && $by){
                $arOrder = [$order => $by];
            }
        }
        
        $obBuilder->orderBy($arOrder);
        /*Apply Request Sort*/
        
        /*Apply Request Filter*/
        if(is_array($arParams["filter"])){
            $arFields = $obBuilder->getFields();

            foreach($arParams["filter"] AS $fieldName => $value){
                if(isset($arFields[$fieldName])){
                    $arFields[$fieldName]->filter($value);
                }
            }
        }
        /*Apply Request Filter*/
                  
        if($arParams["pagination"]){
            $obBuilder->pagination($arParams["pagination"]);
        }

        return $obBuilder->fetchAll();
    }
    
    static public function expr($expr){
        return new Expr($expr);
    }
}
?>