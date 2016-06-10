<?
namespace Core\Cache;

class CHTMLCache extends CFileCache{
    protected $path     = '/cache/html';    
    protected $preffix  = 'html_';
    protected $start    = false;
    
    /**
     * Начало буферизации
     */
    public function start(){
        $this->start = true;
        ob_start();
    }
    
    /**
     * Окончание буферизации
     * Возвращает содержимое буфера
     * Работает вместе с методом start()
     */
    public function end(){
        if($this->start){
            $this->start = false;
            return $this->set(ob_get_clean());
        }else{
            return false;
        }
    }
}

/*
$CHTMLCache = new CHTMLCache;

if($CHTMLCache->exist('MyHash', 30)){ //30 sec
    $html = $CHTMLCache->get();
    
    echo 'Cache';
    
}else{
    $CHTMLCache->start();
    
    echo 'MY Example Buffer HTML';
    
    $html = $CHTMLCache->end();
    
    echo 'No Cache';
}
p($html);
*/
?>