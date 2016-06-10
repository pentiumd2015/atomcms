<?
use \DB\Connection;

class CSession implements SessionHandlerInterface{
    private static $_instance;
    
    static public function save(){
        if(self::$_instance){
            self::$_instance->write(static::getSessionID(), session_encode());
        }
    }
    
    static public function getSessionID(){
        return session_id();
    }
    
    static public function start(){
        if(!self::$_instance){
            self::$_instance = new self;
            
            register_shutdown_function("session_write_close");
            
            session_set_save_handler(self::$_instance, true);
            session_start();
        }
    }
    
    /**
     * устанавливаем значение указанному ключу сессии 
     */
    static public function set($key, $value = NULL){
        $_SESSION[$key] = $value;
    }
    
    /**
     * получаем значение указанного ключа сессии
     */
    static public function get($key){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL ;
    }
    
    static public function getAll(){
        return $_SESSION;
    }
    
    static public function clear($key){
        unset($_SESSION[$key]);
    }
    
    static public function clearAll(){
       // session_unset();
        session_destroy();
    }
    
    public function __construct(){
        $this->db = Connection::getInstance();
    }
    
    public function close(){
        return true;
    }
    /*
    public function create_sid(){
        return session_regenerate_id();
    }*/
    
    public function destroy($sessionID){
        $this->db->query("DELETE 
                          FROM user_session 
                          WHERE session_id=?", array($sessionID));
        return true;
    }
    
    public function gc($maxlifetime){
        return $this->db->query("DELETE 
                                 FROM user_session 
                                 WHERE expire<?", array(time()));
        return true;
    }
    
    public function open($savePath, $sessionName){
        return true;
    }
    
    public function read($sessionID){
        $arSession = $this->db->query("SELECT data, expire
                                       FROM user_session 
                                       WHERE session_id=?", array($sessionID))
                              ->fetch(PDO::FETCH_ASSOC);
        
        return $arSession && $arSession["expire"] > time() ? $arSession["data"] : NULL;
    }
    
    public function write($sessionID, $sessionData){
        $sessionMaxLifeTime = get_cfg_var("session.gc_maxlifetime");
        
        if(!$sessionMaxLifeTime){
            $sessionMaxLifeTime = 1440;
        }
        
        $arSession = $this->db->query("SELECT session_id, remember
                                       FROM user_session 
                                       WHERE session_id=?", array($sessionID))
                              ->fetch(PDO::FETCH_ASSOC);
        
        $arData = array("data" => $sessionData);
        
        if($arSession){
            if(!$arSession["remember"]){
                $arData["expire"] = time() + $sessionMaxLifeTime;
            }
            
            $this->db->update("user_session", $arData, "session_id=?", array($arSession["session_id"]));
            
            return $arSession["session_id"];
        }else{
            $arData["expire"]       = time() + $sessionMaxLifeTime;
            $arData["session_id"]   = $sessionID;
            
            return $this->db->insert("user_session", $arData);
        }
    }
}
?>