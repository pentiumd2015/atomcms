<?
namespace Core\Cache;

class TableCache{
    protected $connection;
    protected $tableName = 'cache_table';
    protected $value;
    protected $hash;
    protected $expire = -1;
    
    public function __construct($connection = NULL){
        $this->connection = $connection ? $connection : \Core\DB\Connection::getInstance();
    }
    
    public function createCacheTable(){
		$dbType = $this->connection->getDbType();
        
        switch($dbType){
            case 'mysql':
                $blob = 'LONGBLOB';
                
                break;
            case 'pgsql':
                $blob = 'BYTEA';
                
                break;
            default:
                $blob = 'BLOB';
                
                break;
        }
            
		$this->connection->query('CREATE TABLE IF NOT EXISTS ' . $this->tableName . '(
                                     id CHAR(128) PRIMARY KEY,
                                     expire INTEGER,
                                     value ' . $blob . '
                                  )');
                                  
        $this->connection->query('CREATE INDEX idx_expire ON ' . $this->tableName . ' (expire)');
	}
    
    public function set($value = ''){
        if($this->hash && $this->expire){
            if($this->expire > 0){
                $this->expire+= time();
            }else{
                $this->expire = 0;
            }
            
            $this->value = $value;
            //$this->connection->query('DELETE FROM ' . $this->tableName . ' WHERE id=?', array($this->hash));
            $this->connection->query('INSERT INTO ' . $this->tableName . ' (id, expire, value) VALUES(?, ?, ?)', array($this->hash, $this->expire , $this->value));
            
            return $this->value;
        }else{
            return false;
        }
    }

    public function get(){
        return $this->value;
    }
    
    public function exist($hash, $expire = 0){
        $this->hash = sha1($hash);
        
        $this->expire = (int)$expire;
        
        $sql = 'SELECT 
                    value 
                FROM ' . $this->tableName . '
                WHERE id=?
                AND (expire=0 OR expire>?)';
        
        $obResult = $this->connection->query($sql, array($this->hash, time()))->fetch();
        
        if($obResult){
            $this->value = $obResult->value;
            return true;
        }else{
            $this->connection->query('DELETE FROM ' . $this->tableName . ' WHERE id=?', array($this->hash));
            return false;
        }
    }
}
?>