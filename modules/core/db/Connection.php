<?
namespace DB;

use \CEvent;
use \PDOException;

class Connection{
    protected $pdo;
    protected $result;
    
    public function getPDO(){
        return $this->pdo;
    }
    
    public function __construct(array $params){
        $this->dsn      = $params["dsn"];
        $this->username = $params["username"];
        $this->password = $params["password"];
        
        CEvent::trigger("CORE.DB.CONNECT.BEFORE", [$this]);
        
        try{
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $params["attributes"]);
        }catch(PDOException $e){
            throw new PDOException($e->getMessage(), $e->errorInfo, $e->getCode(), $e);
        }
        
        CEvent::trigger("CORE.DB.CONNECT.AFTER", [$this]);
        
        return $this;
    }
    
    public function getDbType(){
        if(($pos = strpos($this->dsn, ":")) !== false){
            return strtolower(substr($this->dsn, 0, $pos));
        }
    }
    
    static public function getBuilder(){
        return new Builder($this);
    }
    
    public function query($sql, $statements = []){
        try{
            CEvent::trigger("CORE.DB.QUERY.BEFORE", [$this, $sql, $statements]);
            
            $this->result = $this->pdo->prepare($sql);
            $this->result->execute($statements);
            
            CEvent::trigger("CORE.DB.QUERY.AFTER", [$this, $sql, $statements]);
            
            return $this->result;
        }catch(PDOException $e){
            CEvent::trigger("CORE.DB.QUERY.ERROR", [$this, $e]);
        }
    }
    
    public function setAttributes(array $attributes){
        foreach($attributes AS $attribute => $value){
            $this->pdo->setAttribute($attribute, $value);
        }

        return $this;
    }
    
    public function setAttribute($attribute, $value){
        $this->pdo->setAttribute($attribute, $value);

        return $this;
    }
    
    public function beginTransaction(){
        $this->pdo->beginTransaction();
        
        return $this;
    }
    
    public function rollBack(){
        $this->pdo->rollBack();
        
        return $this;
    }
    
    public function commit(){
        $this->pdo->commit();
        
        return $this;
    }
    
    public function freeResult(){
        $this->result = NULL;
    }
    
    public function quoteTable($table){
        if(strpos(strtoupper($table), " AS ") !== false){ // with alias
            list($tableName, , $tableAlias) = explode(" ", $tableName, 3);
            $table = $this->_quoteTable($tableName) . " AS " . $this->quoteColumn($tableAlias);
        }else{
            $table = $this->_quoteTable($table);
        }
        
        return $table;
    }
    
    public function quoteColumn($column){
        $columnName     = $column;
        $columnTable    = null;
        
        if(strpos($column, ".") !== false){
            list($columnTable, $columnName) = explode(".", $column, 2);
        }
        
        $columnAlias = null;
        
        if(stripos($columnName, " as ") !== false){ //if alias
            list($columnName, $columnAlias) = preg_split("/\s+as\s+/si", $columnName, 2, PREG_SPLIT_NO_EMPTY);
        }
        
        $column = $this->_quoteColumn($columnName);
        
        if($columnTable){
            $column = $this->quoteTable($columnTable) . $column;
        }
        
        if($columnAlias){
            $column.= " AS " . $this->_quoteColumn($columnAlias);
        }
        
        return $column;
    }
    
    protected function _quoteTable($tableName){
        $parts = (strpos($tableName, ".") === false) ? [$tableName] : explode(".", $tableName) ;
        
        foreach($parts AS $i => $part){
            $parts[$i] = (strpos($part, "`") !== false ? $part : "`" . $part . "`");
        }
        
        return implode(".", $parts);
    }

    protected function _quoteColumn($columnName){
        return strpos($columnName, "`") !== false || $columnName === "*" ? $columnName : "`" . $columnName . "`";
    }
    
}
?>