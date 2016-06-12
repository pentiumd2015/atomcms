<?
namespace Application;

use \CAtom;

class CBaseApplication extends \CServiceLocator{
    public $config = null;
    
    public function __construct(array $config = []){
        CAtom::$app = $this;
        
        $this->config = $config;

        $this->init();
    }
    
    public function init(){
        
    }
    
    public function end(){
        CEvent::trigger("CORE.END", array($this));
        exit;
    }
}