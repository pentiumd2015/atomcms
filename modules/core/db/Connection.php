<?
namespace DB;

use \CEvent;
use \CException;

class Connection{
    protected $driver;
    protected static $obj;
    
    /**
     * Singleton
     */
    public static function getInstance(){
        if(!isset(self::$obj)){
            $obReflection  = new \ReflectionClass(__CLASS__);
            self::$obj = $obReflection->newInstanceArgs(func_get_args());
        }
        
        return self::$obj;
    }
    
    static protected $arDrivers = array(
		"mysql" => "\DB\Driver\MysqlDriver"
	);
    
    public function setDrivers(array $arDrivers){
        $this->arDrivers = array_merge($this->arDrivers, $arDrivers);
        
        return $this;
    }
    
    public function getDrivers(){
        return $this->arDrivers;
    }
    
    public function __construct($dsn, $username, $password){
        $this->dsn      = $dsn;
        $this->username = $username;
        $this->password = $password;
        
        $dbType = $this->getDbType();

        if(isset(self::$arDrivers[$dbType])){
            $className = self::$arDrivers[$dbType];
            
            if(class_exists($className, true)){
                CEvent::trigger("CORE.DB.CONNECT.BEFORE", array($this));
                
                try{
                    $this->driver = new $className($this->dsn, $this->username, $this->password);
                }catch(Exception $e){
                    CEvent::trigger("CORE.DB.CONNECT.ERROR", array($this, $e));
                }
                
                CEvent::trigger("CORE.DB.CONNECT.AFTER", array($this));
            }else{
                throw new CException("DB driver [" . $className . "] not found");
            }
        }else{
            throw new CException("DB driver [" . $className . "] not available");
        }
        
        return $this;
    }
    
    public function getDbType(){
        if(($pos = strpos($this->dsn, ":")) !== false){
            return strtolower(substr($this->dsn, 0, $pos));
        }
    }
    
    public function getDriver(){
        return $this->driver;
    }
    
    static public function table($table){
		return static::getBuilder()->from($table);
	}
    
    static public function getBuilder(){
        $obConnection = static::getInstance();
        return new Builder($obConnection);
    }
    
    public function query($sql, $arStatements = array()){
        try{
            CEvent::trigger("CORE.DB.QUERY.BEFORE", array($this, $sql, $arStatements));
            
            $obStatement = $this->driver->query($sql, $arStatements);
            
            CEvent::trigger("CORE.DB.QUERY.AFTER", array($this, $sql, $arStatements));
            
            return $obStatement;
        }catch(\PDOException $e){
            CEvent::trigger("CORE.DB.QUERY.ERROR", array($this, $e));
        }
    }
    
    public function setAttributes(array $arAttributes){
        foreach($arAttributes AS $attribute => $value){
            $this->driver->setAttribute($attribute, $value);
        }

        return $this;
    }
    
    public function setAttribute($attribute, $value){
        $this->driver->setAttribute($attribute, $value);

        return $this;
    }
    
    public function beginTransaction(){
        $this->driver->beginTransaction();
        
        return $this;
    }
    
    public function rollBack(){
        $this->driver->rollBack();
        
        return $this;
    }
    
    public function commit(){
        $this->driver->commit();
        
        return $this;
    }
    
    public function __call($method, $arParams){
		return call_user_func_array(array($this->driver, $method), $arParams);
	}
}
?>