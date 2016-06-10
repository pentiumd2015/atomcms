<?
namespace \DB\Schema;

abstract class Schema{
    protected $connection;
    
    public function __construct(Connection $connection){
        $this->connection = $connection;
        
        return $this;
    }
    
    public function getConnection(){
		return $this->connection;
	}

	public function setConnection(Connection $connection){
		$this->connection = $connection;
        
		return $this;
	}
    
    abstract public function createTable($tableName, $arParams);
    abstract public function renameTable($tableName, $newTableName);
    abstract public function dropTable($tableName);
    
    abstract public function addColumn($tableName, $columnName, $arParams);
    abstract public function alterColumn($tableName, $columnName, $arParams);
    abstract public function renameColumn($tableName, $columnName, $newColumnName);
    abstract public function dropColumn($tableName, $columnName);
    
    abstract public function getColumns($tableName);
}
?>