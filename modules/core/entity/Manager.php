<?
namespace Entity;

use DB\Manager AS DbManager;

abstract class Manager extends DbManager{
    protected static $entityName;
    
    protected static $info = [];
    
    protected static $events = [
        "ADD"       => "ENTITY.ADD",
        "UPDATE"    => "ENTITY.UPDATE",
        "DELETE"    => "ENTITY.DELETE",
    ];
    
    abstract public function getFields();
    
    public static function getEntityName(){
        return static::$entityName ? static::$entityName : static::$tableName ;
    }
    
    public static function query(){
        return (new Query(new static))->from(static::getTableName());
    }
    
    public static function getInfo(){
        return static::$info;
    }
    
    public static function setInfo(array $info){
        static::$info = $info;
    }
    
    public static function getPk(){
        if(static::$primaryKey === null){
            foreach((new static)->getFields() AS $field){
                if(!$field instanceof Field\Scalar\Field){
                    continue;
                }
                
                if($field->primary){
                    static::$primaryKey = $field->getName();
                    break;
                }
            }
            
            if(static::$primaryKey === null){
                static::$primaryKey = "id";
            }
        }
        
        return static::$primaryKey;
    }
    
    public static function search(array $params = []){
        $query = static::query();
        
        $sort = [];
        
        /*Apply Request Sort*/
        if(isset($params["sort"]) && is_array($params["sort"])){
            $order  = key($params["sort"]);
            $by     = reset($params["sort"]);
            
            if($order && is_string($order) && $by && is_string($by)){
                $sort = [$order => $by];
            }
        }

        $query->orderBy($sort);
        /*Apply Request Sort*/

        /*Apply Request Filter*/
        if(isset($params["filter"]) && is_array($params["filter"])){
            $fields = $query->getFields();

            foreach($params["filter"] AS $fieldName => $value){
                if(isset($fields[$fieldName]) && $fields[$fieldName]->filterable){
                    $fields[$fieldName]->filter($value);
                }
            }
        }
        /*Apply Request Filter*/

        return $query;
    }
}