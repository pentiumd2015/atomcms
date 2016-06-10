<?
namespace Core\Cache;

class CFileCache{
    protected $path     = '/cache';
    protected $preffix  = '';
    protected $hash;

    public function __construct($path = NULL){
        $this->path = !$path ? ROOT_PATH . $this->path : $path ;
    }
    
    public function setPath($path){        
        $this->path = $path;
        
        return $this;
    }
    
    public function getPath(){
        return $this->path;
    }
    
    public function getFullPath(){
        return $this->hash ? $this->path . '/' . $this->preffix . $this->hash : false;
    }
    
    public function set($data = NULL, $chmod = 755){
        $file = $this->getFullPath();
        
        if(!is_dir($this->path)){
            mkdir($this->path, $chmod, true);
        }

        if($file && is_file($file) && !is_writable($file)){
            return false;
        }else{
            file_put_contents($file, $data, LOCK_EX);
            
            return $data;
        }
    }
    
    /**
     * Получаем кеш из файла
     */
    public function get(){
        $file = $this->getFullPath();
        
        if($file && is_file($file)){
            return file_get_contents($file);
        }else{
            return false;
        }
    }
    
    /**
     * Проверяем вышло ли время кеша, либо наличие файла кеша
     */
    public function exist($hash, $expire = -1){
        $this->hash = sha1($hash);
        
        $file = $this->getFullPath();
        
        if($file && is_file($file)){
            if($expire == -1){ //permanent cache
                return true;
            }else if($expire > 0){
                return time() - $expire < filemtime($file);
            }
        }else{
            return false;
        }
    }
}
?>