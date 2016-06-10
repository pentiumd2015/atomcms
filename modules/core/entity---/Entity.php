<?
namespace Entity;

/*TO DO 
сделать валидацию доп полей
*/

use \DB\Connection;
use \DB\Builder AS DbBuilder;
use \CArrayHelper;
use \CArrayFilter;

abstract class Entity{
    static protected $_table;
    static protected $_entityName;
    static protected $_pk = "id";
    
    static protected $arInfo = array();
    
    const FIELD_VALUE_TABLE = "new_entity_extra_field_value";
    
    static protected $arEvents = array(
        "ADD"       => "ENTITY.ADD",
        "UPDATE"    => "ENTITY.UPDATE",
        "DELETE"    => "ENTITY.DELETE",
    );
    
    abstract public function getFields();
    
    public function getCustomFields(){
        return array();
    }
    
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
        $obBuilder = new Builder(Connection::getInstance(), new static);

        return $obBuilder->from(static::getTableName());
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
        return static::$_pk;
    }
    
    static public function setPk($pk){
        static::$_pk = $pk;
    }
    
    public function onBeforeAdd(array $arData){
        return $arData;
    }
    
    public function onBeforeUpdate(array $arData){
        return $arData;
    }
    
    public function onBeforeDelete(array $arData, $id){
        return $arData;
    }
    
    public function onAfterAdd(array $arData, $id){
        
    }
    
    public function onAfterUpdate(array $arData, $id){
        
    }
    
    public function onAfterDelete(array $arData, $id){
        
    }
    
    static public function getByID($id, $loadExtraFields = false){
        $obBuilder = static::builder();
        
        if($loadExtraFields){
            $obBuilder->select("*", "f_*");
        }else{
            $obBuilder->select("*");
        }
        
        return $obBuilder->where(static::getPk(), $id)->fetch();
    }
    
    static public function getAllByID($arIDs, $loadExtraFields = false){
        $obBuilder = static::builder();
        
        if($loadExtraFields){
            $obBuilder->select("*", "f_*");
        }else{
            $obBuilder->select("*");
        }
        
        return $obBuilder->whereIn(static::getPk(), $arIDs)->fetchAll();
    }
    
    static public function add(array $arData){
        return static::builder()->add($arData);
    }
    
    static public function update($id, array $arData){
        $obResult = static::builder()->where(static::getPk(), $id)
                                     ->update($arData);
        
        if($obResult->isSuccess()){
            if(($arIDs = $obResult->getID()) && is_array($arIDs)){
                $obResult->setID(reset($arIDs));
            }
        }
        
        return $obResult;
    }
    
    static public function updateAll(array $arIDs, array $arData){
        return static::builder()->whereIn(static::getPk(), $arIDs)
                                ->update($arData);
    }
    
    static public function delete($id){
        $obResult = static::builder()->where(static::getPk(), $id)
                                     ->delete();
        
        if($obResult->isSuccess() && ($arIDs = $obResult->getID()) && is_array($arIDs)){
            $obResult->setID(reset($arIDs));
        }
        
        return $obResult;
    }
    
    static public function deleteAll(array $arIDs){
        return static::builder()->whereIn(static::getPk(), $arIDs)
                                ->delete();
    }
    
    static public function search($arParams = array()){
        $obEntity       = new static;
        $pk             = $obEntity->getPk();
        $fieldPk        = ExtraField::getPk();
        $tableName      = $obEntity->getTableName();
        
        $obBuilder = static::builder()->select($arParams["select"]);//->select("*", "f_*", "groups", "access");

        $arFields = array();
        
        foreach($obEntity->getFields() AS $obField){
            $arFields[$obField->getFieldName()] = $obField;
        }
        
        $arCustomFields = array();
        
        foreach($obEntity->getCustomFields() AS $obCustomField){
            $arCustomFields[$obCustomField->getFieldName()] = $obCustomField;
        }
        
        $arExtraFields = CArrayHelper::index($obEntity->getExtraFields(), $fieldPk);
        
        foreach($arExtraFields AS $index => $arExtraField){
            $fieldTypeClass = $arExtraField["type"];
            
            if(class_exists($fieldTypeClass)){
                $fieldName      = ExtraField::getFieldNameById($arExtraField[$fieldPk]);
                $obFieldType    = new $fieldTypeClass($fieldName, $arExtraField, $obEntity);
                
                $arExtraFields[$index]["type"] = $obFieldType;
            }else{
                unset($arExtraFields[$index]);
            }
        }
        
        /*Apply Request Sort*/
        if($arParams["sort"]){
            $sortField = key($arParams["sort"]);
            
            if(!$sortField){
                $sortField = $pk;
            }
            
            $sortBy = htmlspecialchars(reset($arParams["sort"]));
            $sortBy = (strtoupper($sortBy) == "ASC") ? "ASC" : "DESC" ;
            
            if($arFields[$sortField]){
                $arFields[$sortField]->orderBy($sortBy, $obBuilder);
            }else if($arCustomFields[$sortField]){
                $arCustomFields[$sortField]->orderBy($sortBy, $obBuilder);
            }else if(($fieldID = ExtraField::getFieldIdByName($sortField))){
                if($arExtraFields[$fieldID]){
                    $arExtraFields[$fieldID]["type"]->orderBy($sortBy, $obBuilder);
                }
            }
        }else{
            $obBuilder->orderBy($tableName . "." . $pk, "DESC");
        }
        /*Apply Request Sort*/
        
        /*Apply Request Filter*/
        if(is_array($arParams["filter"])){
            foreach($arParams["filter"] AS $fieldName => $value){
                if($arFields[$fieldName]){
                    $arFields[$fieldName]->filter($value, $obBuilder);
                }else if($arCustomFields[$fieldName]){
                    $arCustomFields[$fieldName]->filter($value, $obBuilder);
                }else if(($fieldID = ExtraField::getFieldIdByName($fieldName))){
                    if($arExtraFields[$fieldID]){
                        $arExtraFields[$fieldID]["type"]->filter($arParams["filter"][$fieldName], $obBuilder);
                    }
                }
            }
        }
        /*Apply Request Filter*/
                  
        if($arParams["pagination"]){
            $obBuilder->pagination($arParams["pagination"]);
        }

        return $obBuilder->fetchAll();
    }
    
    static public function getExtraFieldValues(array $arItemIDs, $arFieldIDs = array()){
        $fieldPk        = ExtraField::getPk();
        $fieldTableName = ExtraField::getTableName();
        
        $obBuilder = static::getExtraFieldValueBuilder();
        
        $obBuilder->whereIn("item_id", $arItemIDs);
        
        if($arFieldIDs){
            if(!is_array($arFieldIDs)){
                $arFieldIDs = array($arFieldIDs);
            }
            
            $arTmpFieldIDs  = array();
            $arFieldAliases = array();
            
            foreach($arFieldIDs AS $fieldKey){
                if(is_numeric($fieldKey)){
                    $arTmpFieldIDs[] = $fieldKey;
                }else if(is_string($fieldKey)){
                    $arFieldAliases[] = $fieldKey;
                }
            }
            
            if(count($arFieldAliases)){
                $arExtraFieldsByAlias = ExtraField::builder()->select($fieldPk)
                                                             ->whereIn("alias", $arFieldAliases)
                                                             ->fetchAll();
                
                foreach($arExtraFieldsByAlias AS $arExtraField){
                    $arTmpFieldIDs[] = $arExtraField[$fieldPk];
                }
            }
            
            $obBuilder->whereIn("extra_field_id", $arTmpFieldIDs);
        }
        
        $obBuilder->orderBy("id", "ASC");

        return $obBuilder->fetchAll();
    }
    
    static public function setExtraFieldValues(array $arItemIDs, array $arFieldValues){
        $fieldPk        = ExtraField::getPk();
        $entityName     = static::getEntityName();
        
        $arExtraFields  = CArrayHelper::index(static::getExtraFields(array_keys($arFieldValues)), $fieldPk);
        $arFieldAliasID = CArrayHelper::getKeyValue($arExtraFields, "alias", $fieldPk);
        
        $obEntity       = new static;
       
        foreach($arExtraFields AS $index => $arExtraField){
            $fieldTypeClass = $arExtraField["type"];
            
            if(class_exists($fieldTypeClass)){
                $obExtraField                           = new $fieldTypeClass(ExtraField::getFieldNameById($arExtraField[$fieldPk]), $arExtraField, $obEntity);
                $arExtraFields[$index]["type"]          = $obExtraField;
                $arExtraFields[$index]["column_value"]  = $obExtraField->getColumnForValue();
            }else{
                unset($arExtraFields[$index]);
            }
        }
        
        //get field values from database with hash key (item_id, field_id, value)
        $arExistFieldValues = array();
        
        foreach(static::getExtraFieldValues($arItemIDs, array_keys($arExtraFields)) AS $arExistFieldValue){
            $arExtraField = $arExtraFields[$arExistFieldValue["extra_field_id"]];
            
            if($arExtraField){
                $uid = $arExistFieldValue["item_id"] . ":" . $arExistFieldValue["extra_field_id"] . ":" . $arExistFieldValue[$arExtraField["column_value"]]; 
            
                $arExistFieldValues[$uid][] = $arExistFieldValue;
            }
        }

        $arNewFieldValueIDs = array();
       
        //for each request field
        foreach($arExtraFields AS $fieldID => $arField){
            if(strlen($arField["alias"]) && isset($arFieldValues[$arField["alias"]])){
                $fieldID                    = $arFieldAliasID[$arField["alias"]];
                $arFieldValues[$fieldID]    = $arFieldValues[$arField["alias"]];
                unset($arFieldValues[$arField["alias"]]);
            }
      
            if(!is_array($arFieldValues[$fieldID])){
                $arFieldValues[$fieldID] = array($arFieldValues[$fieldID]);
            }
            
            if(!$arField["multi"] && count($arFieldValues[$fieldID]) > 1){
                $arFieldValues[$fieldID] = array(reset($arFieldValues[$fieldID]));
            }
            
            $arFieldValues[$fieldID] = CArrayFilter::filter(/*array_unique(*/$arFieldValues[$fieldID]/*)*/, function($value){
                return (strlen($value) > 0);
            });
            
            $obBuilder = static::getExtraFieldValueBuilder();

            //for each request field value
            foreach($arFieldValues[$fieldID] AS $fieldValue){
                //we are get hash key
                foreach($arItemIDs AS $id){
                    $uid = $id . ":" . $fieldID . ":" . $fieldValue;
                    
                    //and check if not yet exist in $arExistFieldValues, then add new value
                    if(!isset($arExistFieldValues[$uid])){
                        $arValue = array(
                            "entity_id"                 => $entityName,
                            "item_id"                   => $id,
                            "extra_field_id"            => $fieldID,
                            $arField["column_value"]    => $fieldValue
                        );
                        
                        if(($newValueID = $obBuilder->insert($arValue))){
                            $arNewFieldValueIDs[] = $newValueID;
                        }
                    }
                    
                    //unset value for delete other values which not set
                    if(count($arExistFieldValues[$uid]) > 1){
                        array_pop($arExistFieldValues[$uid]);
                    }else{
                        unset($arExistFieldValues[$uid]);
                    }
                }
            }
        }
        
        //delete other values which not set
        if(count($arExistFieldValues)){
            $arExistFieldValues     = array_values($arExistFieldValues);
            $arDeleteFieldValueIDs  = array();
         
            foreach($arExistFieldValues AS $arExistFieldValue){
                foreach($arExistFieldValue AS $arExistFieldValueItem){
                    $arDeleteFieldValueIDs[] = $arExistFieldValueItem["id"];
                }
            }
            
            if(count($arDeleteFieldValueIDs)){
                $obBuilder = static::getExtraFieldValueBuilder();
                $obBuilder->whereIn("id", $arDeleteFieldValueIDs)
                          ->delete();
            }
        }
        
        $obResult = new Result\AddResult;
        $obResult->setSuccess(true);
        $obResult->setID($arNewFieldValueIDs);
        
        return $obResult;
    }
    
    static private function getExtraFieldValueBuilder(){
        $obBuilder = new DbBuilder(Connection::getInstance());
        return $obBuilder->from(self::FIELD_VALUE_TABLE)
                         ->where("entity_id", static::getEntityName());
    }
    
    static public function getExtraFields(array $arFieldIDs = array()){
        $fieldPk = ExtraField::getPk();
        
        $arTmpFieldIDs  = array();
        $arFieldAliases = array();

        foreach($arFieldIDs AS $fieldKey){
            if(is_numeric($fieldKey)){
                $arTmpFieldIDs[] = $fieldKey;
            }else if(is_string($fieldKey)){
                $arFieldAliases[] = $fieldKey;
            }
        }
        
        $obBuilder = ExtraField::builder()->where("entity_id", static::getEntityName());
        
        if(count($arFieldIDs)){
            $obBuilder->where(function($obBuilder) use($fieldPk, $arTmpFieldIDs, $arFieldAliases){
                if(count($arTmpFieldIDs)){
                    $obBuilder->whereIn($fieldPk, $arTmpFieldIDs, "OR");
                }
                
                if(count($arFieldAliases)){
                    $obBuilder->whereIn("alias", $arFieldAliases, "OR");
                }
            });
        }
        
        return $obBuilder->orderBy($fieldPk, "ASC")
                         ->fetchAll();
    }
    
    static public function addExtraFields(array $arFields){
        $obResult   = new Result\AddResult;
        $entityName = static::getEntityName();
        $arIDs      = array();
        $arErrors   = array();
        
        foreach($arFields AS $key => $arData){
            $arData["entity_id"] = $entityName;
            $obFieldResult = ExtraField::builder()->add($arData);
            
            if($obFieldResult->isSuccess()){
                $arIDs[] = $obFieldResult->getID();
            }else{
                $arErrors[] = array(
                    "data"  => $arData,
                    "errors"=> $obFieldResult->getErrors()
                );
            }
        }
        
        if(count($arErrors)){
            $obResult->setSuccess(false);
            $obResult->setErrors($arErrors);
        }else{
            $obResult->setSuccess(true);
            $obResult->setID($arIDs);
        }
        
        return $obResult;
    }
    
    static public function setExtraFields(array $arFields = array()){
        ExtraField::builder()->where("entity_id", static::getEntityName())
                             ->delete(); //delete all fields
        
        return static::addExtraFields($arFields); //add new fields
    }
    
    static public function getExtraFieldVariants($arFieldIDs = array()){
        return ExtraFieldVariant::builder()->whereIn("extra_field_id", $arFieldIDs)
                                           ->orderBy("priority", "ASC")
                                           ->orderBy("title", "ASC")
                                           ->fetchAll();
    }
}
?>