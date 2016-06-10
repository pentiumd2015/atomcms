<?
namespace DB\Driver;

abstract class Driver extends \PDO{
    protected $obResult;
    
    public function __construct($dsn, $username, $password){
        parent::__construct($dsn, $username, $password);
        
        return $this;
    }
    
    public function query($sql, $arParams = array()){
        $this->obResult = $this->prepare($sql);
        $this->obResult->execute($arParams);
        
        return $this->obResult;
    }

    public function freeResult(){
        $this->obResult = NULL;
    }
    
    abstract public function insert($tableName, $arData);
    abstract public function update($tableName, $arData, $whereSql = "", $arParams = array());
    abstract public function getVersion();
    
    protected function _quoteTable($tableName){
        if(strpos($tableName, ".") === false){
            return (strpos($tableName, "`") !== false ? $tableName : "`" . $tableName . "`");
        }
        
        $arParts = explode(".", $tableName);
        
        foreach($arParts AS $i => &$part){
            $arParts[$i] = (strpos($part, "`") !== false ? $part : "`" . $part . "`");
        }
        
        return implode(".", $arParts);
    }

    public function _quoteColumn($columnName){
        return strpos($columnName, "`") !== false || $columnName === "*" ? $columnName : "`" . $columnName . "`";
    }
    
    public function quoteTable($tableName){
        if(strpos(strtoupper($tableName), " AS ") !== false){ // with alias
            $segments = explode(" ", $tableName);
            $tableName = $this->_quoteTable($segments[0]) . " AS " . $this->quoteColumn($segments[2]);
        }else{
            $tableName = $this->_quoteTable($tableName);
        }
        
        return $tableName;
    }
    
    public function quoteColumn($columnName){
        $prefix = "";
        
        if(($pos = strrpos($columnName, ".")) !== false){
            $prefix     = $this->quoteTable(substr($columnName, 0, $pos)) . ".";
            $columnName = substr($columnName, $pos + 1);
        }
        
        return $prefix . $this->_quoteColumn($columnName);
    }
    
    public function getColumns($tableName){
        return $this->query("SHOW COLUMNS FROM " . $this->quoteTable($tableName))->fetchAll(self::FETCH_ASSOC);
    }
}
?>