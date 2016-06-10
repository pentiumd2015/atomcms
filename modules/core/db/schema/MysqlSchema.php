<?
namespace DB\Schema;

use \DB\Connection;

class MysqlSchema extends Schema{
    static protected $arColumnTypes = array(
        "tinyint" => 1, "smallint" => 1, "mediumint" => 1, "int" => 1, "bigint" => 1, 
        "decimal" => 1, "float" => 1, "double" => 1, "real" => 1, "bit" => 1, "boolean" => 1, "serial" => 1, 
        "date" => 1, "datetime" => 1, "timestamp" => 1, "time" => 1, "year" => 1, 
        "char" => 1, "varchar" => 1, "tinytext" => 1, "text" => 1, "mediumtext" => 1, "longtext" => 1, 
        "binary" => 1, "varbinary" => 1, 
        "tinyblob" => 1, "mediumblob" => 1, "blob" => 1, "longblob" => 1, 
        "enum" => 1, "set" => 1, 
        "geometry" => 1, "point" => 1, "linestring" => 1, "polygon" => 1, "multipoint" => 1, "multilinestring" => 1, "multipolygon" => 1, "geometrycollection" => 1
    );
    
    protected $obResult;

    /*
    public function prepareFields($table, $arFields){ //сделать кеширование, либо вообще удалить метод
        $arNewFields = array();
        
        if(!count($arFields)){
            return $arNewFields;
        }
        
        $arTableFields = $this->query('SHOW COLUMNS FROM ' . $table)->fetchAll();
        
        foreach($arTableFields AS $obField){
            $field = $obField->Field;
            
            if(isset($arFields[$field])){
                $arNewFields[$field] = $arFields[$field];
            }
        }
        
        return $arNewFields;
    }
    */    
    static protected function getSqlColumnParams($columnName, $arParams, $arAvailableFieldParams = array()){
        if(is_array($arParams)){
            if(!count($arAvailableFieldParams)){
                $arAvailableFieldParams = array("type", "length", "null", "ai", "index", "default", "comment");
            }
            
            $arTmpIndexes = array();
            
            $column = $this->getConnection()->quoteColumn($columnName);
            
            $sql = $column;
            
            $hasError = false;
            
            foreach($arAvailableFieldParams AS $param){
                $paramExist = isset($arParams[$param]);
                
                switch($param){
                    case "type":
                        $value = $arParams[$param];
                        
                        if(!$paramExist || !isset(self::$arColumnTypes[$value])){
                            $hasError = true;
                        }else{
                            $sql.= " " . $value;
                        }
                        break;
                    case "length":
                        $value = $arParams[$param];
                        
                        if(is_array($value)){
                            $sql.= "(" . $value[0] . ", " . $value[1] . ")";
                        }else if($value > 0){
                            $sql.= "(" . $value . ")";
                        }
                        
                        break;
                    case "default":
                        if($paramExist && (!isset($arParams["null"]) || $arParams["null"] == 0)){
                            $sql.= " DEFAULT '" . $arParams[$param] . "'";
                        }
                        
                        break;
                    case "null":
                        if($paramExist && !isset($arParams["default"])){
                            $sql.= " " . ($arParams[$param] == 1 ? "DEFAULT NULL" : "NOT NULL");
                        }
                        
                        break;
                    case "ai":
                        if($arParams[$param]){
                            $sql.= " AUTO_INCREMENT";
                        }
                        
                        break;
                    case "index":
                        switch($arParams[$param]){
                            case "primary":
                                $arTmpIndexes[] = "PRIMARY KEY (" . $column . ")";
                                break;
                            case "index":
                                $arTmpIndexes[] = "KEY " . $column . " (" . $column . ")";
                                break;
                            case "unique":                               
                                $arTmpIndexes[] = "UNIQUE KEY " . $column . " (" . $columnName . ")";
                                break;
                        }
                        
                        break;
                    case "comment":
                        if($arParams[$param]){
                            $sql.= " COMMENT '" . $arParams[$param] . "'";
                        }
                        
                        break;
                }
                
                if($hasError){
                    break;
                }
            }
            
            if(!$hasError){
                if(count($arTmpIndexes)){
                    $sql.= ", \n" . implode(", \n", $arTmpIndexes);
                }
                
                return $sql;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    public function createTable($tableName, $arParams){
        if(is_array($arParams["columns"])){
            $connection = $this->getConnection();
            $sql = "CREATE TABLE IF NOT EXISTS " . $connection->quoteTable($tableName);
            
            $arTmpColumns = array();
            
            foreach($arParams["columns"] AS $columnName => $arColumnParams){
                $columnSqlParams = self::getSqlColumnParams($columnName, $arColumnParams);
                
                if($columnSqlParams){
                    $arTmpColumns[] = $columnSqlParams;
                }
            }
            
            if(count($arTmpColumns)){
                $sql.= "\n(" . implode(", \n", $arTmpColumns) . ")";
                
                if($arParams["options"]){
                    $sql.= " \n" . $arParams["options"];
                }
                
                return $connection->query($sql);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    public function renameTable($tableName, $newTableName){
        $connection = $this->getConnection();
        $sql = "RENAME TABLE " . $connection->quoteTable($tableName) . " TO " . $connection->quoteTable($newTableName);
		return $connection->query($sql);
	}
    
    public function dropTable($tableName){
        $connection = $this->getConnection();
        $tableName = is_array($tableName) ? implode(", ", array_map(array($this, "quoteTable", $tableName))) : $connection->quoteTable($tableName);
        return $connection->query("DROP TABLE IF EXISTS " . $tableName);
    }
    
    public function addColumn($tableName, $columnName, $arParams){
        $connection = $this->getConnection();
        $columnSqlParams = self::getSqlColumnParams($columnName, $arParams);

        if($columnSqlParams){
            return $connection->query("ALTER TABLE " . $connection->quoteTable($tableName) . " ADD (" . $columnSqlParams . ")");
        }else{
            return false;
        }
	}
    
    public function alterColumn($tableName, $columnName, $arParams){
        $connection = $this->getConnection();
        $newColumnName      = isset($arParams["name"]) ? $arParams["name"] : $columnName ;
        $columnSqlParams    = self::getSqlColumnParams($newColumnName, $arParams);
        
        if($columnSqlParams){
            $columnSqlParams = " " . $columnSqlParams;
        }

        return $connection->query("ALTER TABLE " . $connection->quoteTable($tableName) . " CHANGE " . $connection->quoteColumn($columnName) . $columnSqlParams);
    }
    
    public function renameColumn($tableName, $columnName, $newColumnName){
        $type   = false;
        $length = false;

        foreach($this->getColumns($tableName) AS $arColumn){
            if($arColumn["Field"] == $columnName){
                if(strpos($arColumn["Type"], "(") !== false){
                    if(preg_match("/^([^\(]+)?\((.+)\)$/i", $arColumn["Type"], $arMatches)){
                        $type   = $arMatches[1];
                        $length = $arMatches[2];
                    }
                }else{
                    $type = $arColumn["Type"];
                }
                
                break;
            }
        }
        
        return self::alterColumn($tableName, $columnName, array(
            "name"      => $newColumnName,
            "type"      => $type,
            "length"    => $length
        ));
    }
    
    public function dropColumn($tableName, $columnName){
        $connection = $this->getConnection();
        return $connection->query("ALTER TABLE " . $connection->quoteTable($tableName) . " DROP COLUMN " .  $connection->quoteColumn($columnName));
	}
    
    public function getColumns($tableName){
        $connection = $this->getConnection();
        return $connection->query("SHOW COLUMNS FROM " . $connection->quoteTable($tableName))->fetchAll(self::FETCH_ASSOC);
    }
}
?>