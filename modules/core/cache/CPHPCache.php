<?
namespace Core\Cache;

class CPHPCache extends CFileCache{
    protected $path     = '/cache/php';
    protected $preffix  = 'php_';
    
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

/*

$CPHPCache = new CPHPCache;

if($CPHPCache->exist('myhash', 30)){ //30 sec
    $arResult = $CPHPCache->get();
    
    echo 'Cache';
    
}else{    
    $arResult = $CPHPCache->set(array("MyKey" => 35));
    
    echo 'No Cache';
}
p($arResult);
*/
?>