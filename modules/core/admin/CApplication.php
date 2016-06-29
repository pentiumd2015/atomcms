<?
namespace Admin;


use Application\CBaseApplication;
use CEvent;

class CApplication extends CBaseApplication{
    public $environment = "admin";
    public $config = null;
    public $route;

    public function init(){
        parent::init();
        
        $this->set([
            "db"        => ["DB\Connection", [$this->config["db"]]],
            "request"   => "Helpers\CHttpRequest",
            "response"  => "Helpers\CHttpResponse",
            "session"   => ["CSession", ["db"]],
            "user"      => "CUser",
            "router"    => ["Admin\CRouter", [$_SERVER["REQUEST_URI"], $this->config["routes"]]],
            "template"  => ["Admin\CTemplate", [$this->config["template"]]],
            "module"    => ["CModule", [$this->config["module"]]],
            "error"     => ["CError", [$this->config["errors"]]],
            "view"      => "CView"
        ]);
    }

    public function run(){
        $this->error->init();
        
        //initialize events
        $eventFile = ROOT_PATH . $this->config["eventFile"];

        if(is_file($eventFile)){
            include($eventFile);
        }

        $this->session->start();
        $this->user->identify();
        
        CEvent::trigger("CORE.START");
        
        //initialize modules
        $this->module->load($this->config["modules"]);

        //initialize route. if route not found, we have exception
        $this->route = $this->router->getMatchRoute();
        
        echo $this->template->render($this->route->pageFile, $this->route->layoutFile);

        CEvent::trigger("CORE.END", [$this]);
    }
}