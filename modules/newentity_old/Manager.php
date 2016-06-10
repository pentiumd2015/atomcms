<?
namespace NewEntity;

use \Helpers\CArrayHelper;
use \DB\SqlBuilder;

class Manager{
    static protected $entityName;
    static protected $pk = 'id';
    
    static protected $connection;
    
    static public function setConnection($connection){
        self::$connection = $connection;
    }
    
    static public function getConnection(){
        if(!self::$connection){
            self::$connection = \DB\Connection::getInstance();
        }
        
        return self::$connection;
    }
    
    static public function setEntityName($entityName){
        static::$entityName = $entityName;
    }
    
    static public function getEntityName(){
        return static::$entityName;
    }
    
    static public function getBaseFields(){
        return array(
            "id" => array(
                "type"      => "integer",
                "primary"   => true,
            )
        );
    }
    
    static protected function joinEntity($sourceEntityAlias, $referenceEntityName, $referenceEntityAlias, $arRelation){
        if(class_exists($referenceEntityName)){
            $connection = self::getConnection();
            
            $obRelationEntity = new $referenceEntityName;

            if($obRelationEntity instanceof Manager){
                $relationTableName  = $connection->quoteTable($obRelationEntity::getEntityName());
                $sourceEntityAlias  = $connection->quoteTable($sourceEntityAlias);
                $relationAliasName  = $connection->quoteTable($referenceEntityAlias);
                
                $joinType = strtoupper($arRelation["joinType"]);
                    
                    switch($joinType){
                        case "LEFT":
                        case "RIGHT":
                        case "INNER":
                            break;
                        default:
                            $joinType = "LEFT";
                            break;
                    }
                
                $join = $joinType . " JOIN " . $relationTableName . " AS " . $relationAliasName;
                
                if(is_array($arRelation["ref"])){
                    $joinOn = "";
                    
                    foreach($arRelation["ref"] AS $key => $value){
                        $value = strtr($value, array(
                            "this." => $sourceEntityAlias . ".",
                            "ref."  => $relationAliasName . "."
                        ));
                        
                        if(is_string($key)){
                            $key = strtr($key, array(
                                "this." => $sourceEntityAlias . ".",
                                "ref."  => $relationAliasName . "."
                            ));
                            
                            $joinOn.= $key . "=" . $value;
                        }else if(is_integer($key)){
                            if($joinOn){
                                if(is_array($arRelation["refParams"])){
                                    $arRefParams = array($value);
                                    
                                    foreach($arRelation["refParams"] AS $refParam){
                                        $arRefParams[] = strtr($refParam, array(
                                            "this." => $sourceEntityAlias . ".",
                                            "ref."  => $relationAliasName . "."
                                        ));
                                    }
                                    
                                    $value = call_user_func_array("sprintf", $arRefParams);
                                }
                                
                                $joinOn.= " AND " . $value;
                            }
                        }
                    }
                    
                    if($joinOn){
                        $join.= " ON(" . $joinOn . ")";
                    }
                }
                
                return $join;
            }
        }
        
        return "";
    }
    
    static public function getList($arParams = array(), $arValues = array()){
        $connection = self::getConnection();
        
        if(is_string($arParams)){
            $arParams = array("condition" => $arParams);
        }
        
        $arParams["table"]  = static::$entityName;
        $arBaseFields       = array();
        $tableName          = $connection->quoteTable(static::$entityName);
        
        foreach(static::getBaseFields() AS $obField){
            $arBaseFields[$obField->getFieldName()] = $obField;
        }
        
        $staticClass        = get_called_class();
        $arJoinEntity       = array();
        $arSelectFields     = $arParams["select"];
        $arParams["select"] = array();
        
        if(!isset($arSelectFields) || $arSelectFields == "*"){
            $arSelectFields = $arBaseFields;
            
            foreach($arBaseFields AS $fieldName => $obField){
                $arSelectFields[$fieldName] = array(
                    "tableName" => $tableName,
                    "field"     => $obField
                );
            }
        }else{
            if(is_string($arSelectFields)){
                $arTmpSelectFields = CArrayHelper::map(explode(",", $arSelectFields), "trim");
            }else{
                $arTmpSelectFields = $arSelectFields;
            }
            
            $arSelectFields = array();
            
            foreach($arTmpSelectFields AS $key => $fieldName){
                $alias = "";
                
                if(is_string($key)){
                    $alias      = $fieldName;
                    $fieldName  = $key;
                }
                
                if(isset($arBaseFields[$fieldName])){
                    $arSelectFields[$fieldName] = array(
                        "tableName" => $tableName,
                        "alias"     => $alias,
                        "field"     => $arBaseFields[$fieldName]
                    );
                }else{
                    if(is_string($fieldName) && strpos($fieldName, ".") !== false){ //если запросили поля из другой сущности
                        $arRelations            = explode(".", $fieldName);
                        $refFieldName           = array_pop($arRelations);
                        
                        $sourceEntity           = static::$entityName;
                        $arRefEntityFields      = $arBaseFields;
                 
                        foreach($arRelations AS $entityRelationName){
                            if(isset($arRefEntityFields[$entityRelationName])){
                                $arRelationParams = $arRefEntityFields[$entityRelationName]->getParams();
                                
                                if(!isset($arJoinEntity[$entityRelationName])){
                                    $arJoinEntity[$entityRelationName] = 1;
                                    $arParams["join"][] = static::joinEntity($sourceEntity, $arRelationParams["entity"], $entityRelationName, $arRelationParams);
                                }
                                
                                if(isset($arRelationParams["entity"])){
                                    $sourceEntity = $arRelationParams["entity"];
                                    
                                    $arRefEntityFields = array();
                                    
                                    foreach($sourceEntity::getBaseFields() AS $obRefEntityField){
                                        $arRefEntityFields[$obRefEntityField->getFieldName()] = $obRefEntityField;
                                    }
                                }
                            }
                        }
                        
                        if(isset($arRefEntityFields[$refFieldName])){
                            $arSelectFields[$refFieldName] = array(
                                "tableName" => $connection->quoteTable($entityRelationName),
                                "alias"     => $alias,
                                "field"     => $arRefEntityFields[$refFieldName]
                            );
                        }
                    }
                }
            }
            
            foreach($arBaseFields AS $fieldName => $obField){
                if($obField instanceof FieldType\Base\RelationField){
                    $arFieldParams = $obField->getParams();
                    
                    if(!isset($arJoinEntity[$fieldName])){
                        $arJoinEntity[$fieldName] = 1;
                        $arParams["join"][] = static::joinEntity($staticClass, $arFieldParams["entity"], $fieldName, $arFieldParams);
                    }
                }
            }
        }
        
        foreach($arSelectFields AS $fieldName => $arSelectParams){
            $obField = $arSelectParams["field"];
            
            if($obField instanceof FieldType\Base\ScalarField){
                $arParams["select"][] = $arSelectParams["tableName"] . "." . $fieldName . ($arSelectParams["alias"] ? " AS " . $arSelectParams["alias"] : "");
            }else if($obField instanceof FieldType\Base\ExprField){
                $arFieldParams = $obField->getParams();
                
                if(is_array($arFieldParams["exprParams"])){
                    $arExprParams = array($arFieldParams["expr"]);
                    
                    foreach($arFieldParams["exprParams"] AS $exprParam){
                        $arExprParams[] = $arSelectParams["tableName"] . "." . $exprParam;
                    }
                    
                    $expr = call_user_func_array("sprintf", $arExprParams);
                }else{
                    $expr = $arFieldParams["expr"];
                }
                
                $arParams["select"][] = "(" . $expr . ") AS " . $fieldName;
            }
        }
        
        $obSqlBuilder = new \DB\SqlBuilder($connection);
        
        if(isset($arParams["pagination"]) && $arParams["pagination"] instanceof \Helpers\Pagination){
            $obPagination = $arParams["pagination"];
            
            if($obPagination->perPage > 0){
                /*get count rows*/
                $arCountParams              = $arParams;
                $arCountParams["select"]    = "COUNT(*)";
                
                unset($arCountParams["order"], 
                      $arCountParams["limit"], 
                      $arCountParams["group"], 
                      $arCountParams["offset"]);
                
                $sql = $obSqlBuilder->getSQL($arCountParams, \DB\SqlBuilder::MODE_SELECT);

                $obPagination->count    = $connection->query($sql, $arValues)->fetchColumn();
                $obPagination->numPage  = ceil($obPagination->count / $obPagination->perPage);
                $obPagination->correctPage();
                /*get count rows*/
                
                $arParams["limit"]  = $obPagination->perPage;
                $arParams["offset"] = ($obPagination->page - 1) * $obPagination->perPage;
            }
        }
        
        $sql            = $obSqlBuilder->getSQL($arParams, \DB\SqlBuilder::MODE_SELECT);
        $obStatement    = $connection->query($sql, $arValues);
        
        $obResult       = new Result\SelectResult($obStatement);
        $obResult->setSuccess(true);
        
        return $obResult;
    }
    
    static public function getByID($id){
        $connection = self::getConnection();
        
        return static::getList(array(
            "condition" => $connection->quoteTable(static::$entityName) . "." . static::$pk . "=?",
            "limit"     => 1
        ), array($id));
    }
    
    static public function getAllByID($arIDs){
        $arIDs = CArrayHelper::map($arIDs, function($item){
            return (int)$item;
        });
        
        if(!count($arIDs)){
            return false;
        }
        
        $connection = self::getConnection();
        
        return static::getList($connection->quoteTable(static::$entityName) . "." . static::$pk . " IN(" . implode(", ", $arIDs) . ")");
    }
    
    static public function getPk(){
        return static::$pk;
    }
    
    static public function setPk($primaryKey){
        static::$pk = $primaryKey;
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
    
    static protected function filterFields($arData){
        $arBaseFields   = static::getBaseFields();
        $arDataFiltered = array();
        
        foreach($arData AS $fieldName => $fieldValue){
            if(isset($arBaseFields[$fieldName]) && !in_array($arBaseFields[$fieldName]["type"], array("expr", "relation"))){
                $arDataFiltered[$fieldName] = $fieldValue;
            }
        }
        
        return $arDataFiltered;
    }
    
    static protected function validateFields($arData){
        $arErrors = array();
        
        foreach(static::getBaseFields() AS $fieldName => $arField){
            if(is_string($arField["type"])){
                $typeField          = $arField["type"];
                $typeField[0]       = strtoupper($typeField[0]);
                $type               = "\\" . __NAMESPACE__ . "\FieldType\\" . $typeField . "Field";
                $obFieldType        = new $type($fieldName, $arField);
                $arValidateResult   = $obFieldType->validate($arData);
                
                if(!$arValidateResult["success"]){
                    $arErrors[$fieldName] = $arValidateResult["error"];
                }
            }
        }
        
        $arResult = array();
        
        if(count($arErrors)){
            $arResult["success"]    = false;
            $arResult["errors"]     = $arErrors;
        }else{
            $arResult["success"]    = true;
        }
        
        return $arResult;
    }
    
    static public function add($arData){
        $arData     = static::filterFields($arData);
        $arValidate = static::validateFields($arData);
        $obResult   = new Result\AddResult;

        if($arValidate["success"]){
            $newID = self::getConnection()->insert(static::$entityName, $arData, false);
            
            $obResult->setSuccess(true);
            $obResult->setID($newID);
            
            if($newID){
                \CEvent::trigger("ENTITY." . strtoupper(static::$entityName) . ".ADD", array($newID, $arData));
            }
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors($arValidate["errors"]);
        }
        
        return $obResult;
    }
    
    static public function update($id, $arData){
        $resItem    = static::getByID($id);
        $obResult   = new Result\UpdateResult;

        if($arOriginalData = $resItem->fetch(\DB\Connection::FETCH_ASSOC)){
            $arData+= $arOriginalData;
            
            $arData     = static::filterFields($arData);
            $arValidate = static::validateFields($arData);
            
            if($arValidate["success"]){
                $arTmp      = array();
                $arValues   = array();
                
                foreach($arData AS $key => $value){
                    if($value instanceof \DB\Expr){
                        $arTmp[]    = "`" . $key . "`=" . $value->getValue();
                    }else{
                        $arTmp[]    = "`" . $key . "`=?";
                        $arValues[] = $value;
                    }
                }
                
                $connection     = self::getConnection();
                $obSqlBuilder   = new \DB\SqlBuilder($connection);
                $sql            = $obSqlBuilder->getSQL(array(
                    "set"       => $arTmp,
                    "update"    => static::$entityName,
                    "condition" => static::$pk . "=?"
                ), \DB\SqlBuilder::MODE_UPDATE);
                
                $arValues[]         = $id;
                $numAffectedRows    = $connection->query($sql, $arValues)->rowCount();
                
                $obResult->setSuccess(true);
                $obResult->setID($id);
                $obResult->setNumAffectedRows($numAffectedRows);
                
                \CEvent::trigger("ENTITY." . strtoupper(static::$entityName) . ".UPDATE", array($id, $arData));
            }else{
                $obResult->setSuccess(false);
                $obResult->setErrors($arValidate["errors"]);
            }
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors(array(
                "id" => "not found"
            ));
        }
        
        return $obResult;
    }
    
    static public function delete($id){
        $resItem    = static::getByID($id);
        $obResult   = new Result\DeleteResult;
        
        if($arItem = $resItem->fetch(\DB\Connection::FETCH_ASSOC)){
            $arParams = array(
                "condition" => static::$pk . "=?",
                "table"     => static::$entityName
            );
            
            $connection         = self::getConnection();
            $obSqlBuilder       = new \DB\SqlBuilder($connection);
            $sql                = $obSqlBuilder->getSQL($arParams, \DB\SqlBuilder::MODE_DELETE);
            $numAffectedRows    = $connection->query($sql, array($id))->rowCount();
            
            $obResult->setSuccess(true);
            $obResult->setID($id);
            $obResult->setNumAffectedRows($numAffectedRows);
            
            \CEvent::trigger("ENTITY." . strtoupper(static::$entityName) . ".DELETE", array($id, $arItem));
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors(array(
                "id" => "not found"
            ));
        }
        
        return $obResult;
    }
}
?>