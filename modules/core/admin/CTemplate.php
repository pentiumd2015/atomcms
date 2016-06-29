<?
namespace Admin;


use CObject;
use Helpers\CFile;
use CEvent;
use CAtom;

class CTemplate extends CObject{
    protected $config = [];
    protected $view = null;
    
    public function __construct(array $config = []){
        $this->config = $config;
    }
    
    public function render($pageFile, $layoutFile = null){
        $view               = CAtom::$app->view;
        $view->templatePath = $this->config["path"];
        $view->baseUrl      = CFile::normalizePath(BASE_URL . "/" . $view->templatePath);
        $view->layoutPath   = CFile::normalizePath($view->templatePath . "/" . $this->config["layoutPath"]);
        $view->pagePath     = CFile::normalizePath($view->templatePath . "/" . $this->config["pagePath"]);
        $view->layoutFile   = $layoutFile;
        $view->pageFile     = $pageFile;

        CEvent::trigger("CORE.TEMPLATE.BEFORE", [$this, $view]);
        
        $content = "";
        
        /*render page*/
        $pageFilePath = CFile::normalizePath($view->pagePath . "/" . $view->pageFile);
        
        if(is_file(ROOT_PATH . $pageFilePath)){
            $content = $view->render(ROOT_PATH . $pageFilePath);
        }else{
            CEvent::trigger("CORE.TEMPLATE.PAGE.NOT_FOUND", [$this, $view]);
        }
        /*render page*/
       
        /*render layout*/
        if($layoutFile){
            $layoutFilePath = CFile::normalizePath($view->layoutPath . "/" . $layoutFile);
            
            if(is_file(ROOT_PATH . $layoutFilePath)){
                $content = $view->render(ROOT_PATH . $layoutFilePath, ["content" => $content]);
            }else{
                CEvent::trigger("CORE.TEMPLATE.LAYOUT.NOT_FOUND", [$this, $view]);
            }
        }
        /*render layout*/
        
        CEvent::trigger("CORE.TEMPLATE.AFTER", [$this, $view]);

        return $view->runDynamic($content);
    }
}
?>