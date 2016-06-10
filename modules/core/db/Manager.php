<?
namespace DB;

use \CConstruct;

class Manager extends CConstruct{
    static protected $_table;
    static protected $_pk = "id";
        
    static protected $connection;
    
    static public function setConnection($connection){
        self::$connection = $connection;
    }
    
    static public function getConnection(){
        if(!self::$connection){
            self::$connection = Connection::getInstance();
        }
        
        return self::$connection;
    }
    
    static public function builder(){
        $connection = self::getConnection();
        $obBuilder  = new Builder($connection);
        
        return $obBuilder->from(static::getTableName());
    }
    
    static public function getTableName(){
        return static::$_table;
    }
    
    static public function setTableName($tableName){
        static::$_table = $tableName;
        
        return $this;
    }
    
    static public function getPk(){
        return static::$_pk;
    }
    
    static public function setPk($pk){
        static::$_pk = $pk;
        
        return $this;
    }
    
    static public function getSafeFields($arFields, $arSafeFields){
        $arTmpFields = array();
        
        foreach($arSafeFields AS $safeField){
            $arTmpFields[$safeField] = 1;
        }
        
        foreach($arFields AS $field => $value){
            if(!isset($arTmpFields[$field])){
                unset($arFields[$field]);
                continue;
            }
            
            if(is_string($arFields[$field]) && !strlen($arFields[$field])){
                $arFields[$field] = NULL;
            }
        }
        
        return $arFields;
    }
    
    static public function update($id, array $arData){
        return static::builder()->where(static::getPk(), $id)
                                ->update($arData);
    }
    
    static public function updateAll(array $arIDs, array $arData){
        return static::builder()->whereIn(static::getPk(), $arIDs)
                                ->update($arData);
    }
    
    static public function add(array $arData){
        return static::builder()->insert($arData);
    }
    
    static public function getByID($id){
        $obBuilder = static::builder();
        
        return $obBuilder->where(static::getPk(), $id)->fetch();
    }
    
    static public function getAllByID(array $arIDs){
        $obBuilder = static::builder();
        
        return $obBuilder->whereIn(static::getPk(), $arIDs)->fetchAll();
    }
    
    static public function delete($id){
        return static::builder()->where(static::getPk(), $id)
                                ->delete();
    }
    
    static public function deleteAll(array $arIDs){
        return static::builder()->whereIn(static::getPk(), $arIDs)
                                ->delete();
    }
}
?>