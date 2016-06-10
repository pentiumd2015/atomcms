<?
namespace Core\Cache;

class CDBCache extends CFileCache{
    protected $path     = '/cache/db';
    protected $preffix  = 'db_';
    
    public function set($data = array(), $chmod = 755){
        $file = $this->getFullPath();
        
        if(!is_dir($this->path)){
            mkdir($this->path, $chmod, true);
        }

        if($file && is_file($file) && !is_writable($file)){
            return false;
        }else{
            file_put_contents($file, serialize($data), LOCK_EX);
            
            return $data;
        }
    }

    public function get(){
        $file = $this->getPath();
        
        if($file && is_file($file)){            
            return unserialize(file_get_contents($file));
        }else{
            return false;
        }
    }
}
?>