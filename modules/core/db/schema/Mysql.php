<?
namespace DB\Schema;

use DB\PDO;


class Mysql extends BaseSchema{
    protected static $columnTypes = [
        "tinyint" => 1, "smallint" => 1, "mediumint" => 1, "int" => 1, "bigint" => 1, 
        "decimal" => 1, "float" => 1, "double" => 1, "real" => 1, "bit" => 1, "boolean" => 1, "serial" => 1, 
        "date" => 1, "datetime" => 1, "timestamp" => 1, "time" => 1, "year" => 1, 
        "char" => 1, "varchar" => 1, "tinytext" => 1, "text" => 1, "mediumtext" => 1, "longtext" => 1, 
        "binary" => 1, "varbinary" => 1, 
        "tinyblob" => 1, "mediumblob" => 1, "blob" => 1, "longblob" => 1, 
        "enum" => 1, "set" => 1, 
        "geometry" => 1, "point" => 1, "linestring" => 1, "polygon" => 1, "multipoint" => 1, "multilinestring" => 1, "multipolygon" => 1, "geometrycollection" => 1
    ];

    protected function getSqlColumnParams($columnName, array $params, $availableFieldParams = []){
        if(!count($availableFieldParams)){
            $availableFieldParams = ["type", "length", "null", "ai", "index", "default", "comment"];
        }
        
        $tmpIndexes = [];
        
        $column = $this->getConnection()->quoteColumn($columnName);
        
        $sql = $column;
        
        $hasError = false;
        
        foreach($availableFieldParams AS $param){
            $paramExist = isset($params[$param]);
            
            switch($param){
                case "type":
                    $value = $params[$param];
                    
                    if(!$paramExist || !isset(self::$columnTypes[$value])){
                        $hasError = true;
                    }else{
                        $sql.= " " . $value;
                    }

                    break;
                case "length":
                    $value = $params[$param];
                    
                    if(is_array($value)){
                        $sql.= "(" . $value[0] . ", " . $value[1] . ")";
                    }else if($value > 0){
                        $sql.= "(" . $value . ")";
                    }
                    
                    break;
                case "default":
                    if($paramExist && (!isset($params["null"]) || $params["null"] == 0)){
                        $sql.= " DEFAULT '" . $params[$param] . "'";
                    }
                    
                    break;
                case "null":
                    if($paramExist && !isset($params["default"])){
                        $sql.= " " . ($params[$param] == 1 ? "DEFAULT NULL" : "NOT NULL");
                    }
                    
                    break;
                case "ai":
                    if($params[$param]){
                        $sql.= " AUTO_INCREMENT";
                    }
                    
                    break;
                case "index":
                    switch($params[$param]){
                        case "primary":
                            $tmpIndexes[] = "PRIMARY KEY (" . $column . ")";
                            break;
                        case "index":
                            $tmpIndexes[] = "KEY " . $column . " (" . $column . ")";
                            break;
                        case "unique":                               
                            $tmpIndexes[] = "UNIQUE KEY " . $column . " (" . $columnName . ")";
                            break;
                    }
                    
                    break;
                case "comment":
                    if($params[$param]){
                        $sql.= " COMMENT '" . $params[$param] . "'";
                    }
                    
                    break;
            }
            
            if($hasError){
                break;
            }
        }
        
        if(!$hasError){
            if(count($tmpIndexes)){
                $sql.= ", \n" . implode(", \n", $tmpIndexes);
            }
            
            return $sql;
        }else{
            return false;
        }
    }
    
    public function createTable($tableName, array $params){
        if(isset($params["columns"]) && is_array($params["columns"])){
            $sql = "CREATE TABLE IF NOT EXISTS " . $this->connection->quoteTable($tableName);
            
            $tmpColumns = [];
            
            foreach($params["columns"] AS $columnName => $columnParams){
                $columnSqlParams = $this->getSqlColumnParams($columnName, $columnParams);
                
                if($columnSqlParams){
                    $tmpColumns[] = $columnSqlParams;
                }
            }
            
            if(count($tmpColumns)){
                $sql.= "\n(" . implode(", \n", $tmpColumns) . ")";
                
                if($params["options"]){
                    $sql.= " \n" . $params["options"];
                }
                
                return $this->connection->query($sql);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    public function renameTable($tableName, $newTableName){
		return $this->connection->query("RENAME TABLE " . $this->connection->quoteTable($tableName) . " TO " . $this->connection->quoteTable($newTableName));
	}
    
    public function dropTable($tableName){
        $tableName = is_array($tableName) ? implode(", ", array_map(array($this->connection, "quoteTable", $tableName))) : $this->connection->quoteTable($tableName);
        return $this->connection->query("DROP TABLE IF EXISTS " . $tableName);
    }
    
    public function addColumn($tableName, $columnName, array $params){
        $columnSqlParams = $this->getSqlColumnParams($columnName, $params);

        if($columnSqlParams){
            return $this->connection->query("ALTER TABLE " . $this->connection->quoteTable($tableName) . " ADD (" . $columnSqlParams . ")");
        }else{
            return false;
        }
	}
    
    public function alterColumn($tableName, $columnName, array $params){
        $newColumnName      = isset($params["name"]) ? $params["name"] : $columnName ;
        $columnSqlParams    = $this->getSqlColumnParams($newColumnName, $params);
        
        if($columnSqlParams){
            $columnSqlParams = " " . $columnSqlParams;
        }

        return $this->connection->query("ALTER TABLE " . $this->connection->quoteTable($tableName) . " CHANGE " . $this->connection->quoteColumn($columnName) . $columnSqlParams);
    }
    
    public function renameColumn($tableName, $columnName, $newColumnName){
        $type   = false;
        $length = false;

        foreach($this->getColumns($tableName) AS $column){
            if($column["Field"] == $columnName){
                if(strpos($column["Type"], "(") !== false){
                    $matches = [];

                    if(preg_match("/^([^\(]+)?\((.+)\)$/i", $column["Type"], $matches)){
                        $type   = $matches[1];
                        $length = $matches[2];
                    }
                }else{
                    $type = $column["Type"];
                }
                
                break;
            }
        }
        
        return $this->alterColumn($tableName, $columnName, [
            "name"      => $newColumnName,
            "type"      => $type,
            "length"    => $length
        ]);
    }
    
    public function dropColumn($tableName, $columnName){
        return $this->connection->query("ALTER TABLE " . $this->connection->quoteTable($tableName) . " DROP COLUMN " .  $this->connection->quoteColumn($columnName));
	}
    
    public function getColumns($tableName){
        return $this->connection->query("SHOW COLUMNS FROM " . $this->connection->quoteTable($tableName))->fetchAll(PDO::FETCH_ASSOC);
    }
}