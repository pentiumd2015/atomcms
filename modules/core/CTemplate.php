<?

class CTemplate extends CObject{
    protected $config = [];
    public $templatePath;
    public $layoutPath;
    public $layoutFile;
    public $pagePath;
    public $pageFile;
    public $content;
    
    static protected $filePath = "/config/template.php";
    
    public function __construct(array $config = []){
        $this->config = $config;
    }
    
    public function getConfig(){
        return $this->config;
    }
    
    static public function getList(){
        $arTemplates = array();
        
        if(is_file(ROOT_PATH . static::$filePath) && ($arTmpTemplates = include(ROOT_PATH . static::$filePath)) && is_array($arTmpTemplates)){
            $arTemplates = $arTmpTemplates;
            
            foreach($arTemplates AS $templateID => &$arTemplate){
                $arTemplate["template_id"] = $templateID;
            }
        }
        
        return $arTemplates;
    }
    
    static protected function saveToFile($arTemplates){
        $arrayString = CArrayHelper::export($arTemplates);

        file_put_contents(ROOT_PATH . static::$filePath, "<?\nreturn " . $arrayString . "\n?>");
    }
    
    static public function getSafeFields($arFields, $arSafeFields){
        $arTmpFields = array();
        
        foreach($arSafeFields AS $safeField){
            $arTmpFields[$safeField] = 1;
        }
        
        foreach($arFields AS $field => $value){
            if(!isset($arTmpFields[$field])){
                unset($arFields[$field]);
                continue;
            }
            
            if(is_string($arFields[$field]) && !strlen($arFields[$field])){
                $arFields[$field] = NULL;
            }
        }
        
        return $arFields;
    }
    
    static public function delete($templateID){
        $arTemplates = static::getList();
        
        if(!is_array($templateID)){
            $arTemplateIDs = array($templateID);
        }else{
            $arTemplateIDs = $templateID;
        }
        
        foreach($arTemplateIDs AS $templateID){
            if($arTemplates[$templateID]){
                unset($arTemplates[$templateID]);
            }
        }
        
        static::saveToFile($arTemplates);
                
        //нужно удалить все роуты для этого сайта
        
        CEvent::trigger("CORE.TEMPLATE.DELETE", array($templateID));
        
        return true;
    }
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function update($templateID, $arData){
        return static::_save($arData, $templateID);
    }
    
    static protected function _save($arData, $templateID = false){        
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$templateID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
            
            if(empty($arData["template_id"])){
                $arErrors["template_id"][] = "Укажите id";
            }
            
            if(empty($arData["path"])){
                $arErrors["path"][] = "Укажите путь";
            }
            
            if(!preg_match("/^[a-zA-Z0-9_-]+$/", $arData["path"])){
                $arErrors["path"][] = "Путь может содержать латинские символы, а также цифры и _-";
            }
            
            if(!preg_match("/^[a-zA-Z0-9_-]+$/", $arData["template_id"])){
                $arErrors["template_id"][] = "id может содержать латинские символы, а также цифры и _-";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
            
            if(isset($arData["template_id"]) && empty($arData["template_id"])){
                $arErrors["template_id"][] = "Укажите id";
            }
            
            if(isset($arData["path"]) && empty($arData["path"])){
                $arErrors["path"][] = "Укажите путь";
            }
            
            if(isset($arData["path"]) && !preg_match("/^[a-zA-Z0-9_-]+$/", $arData["path"])){
                $arErrors["path"][] = "Путь может содержать латинские символы, а также цифры и _-";
            }
        }
        
        if(!count($arErrors)){
            $arTemplates = static::getList();
            
            if($templateID){
                if(!is_array($templateID)){
                    $arTemplateIDs = array($templateID);
                }else{
                    $arTemplateIDs = $templateID;
                }
                
                $arData = static::getSafeFields($arData, array(
                    "path",
                    "title",
                    "description"
                ));
                
                foreach($arTemplateIDs AS $templateID){
                    foreach($arData AS $param => $value){
                        if($arTemplates[$templateID]){
                            $arTemplates[$templateID][$param] = $value;
                        }
                    }
                }
            }else{
                $templateID = $arData["template_id"];
                
                $arData = static::getSafeFields($arData, array(
                    "path",
                    "title",
                    "description"
                ));
                
                $arTemplates[$templateID] = $arData;
            }
            
            static::saveToFile($arTemplates);
            
            if($templateID){
                CEvent::trigger("CORE.TEMPLATE.UPDATE", array($templateID, $arData));
            }else{
                CEvent::trigger("CORE.TEMPLATE.ADD", array($templateID, $arData));
            }
            
            $arReturn["id"]     = $templateID;
            $arReturn["success"]= true;
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
    
    public function process($layoutFile = NULL, $pageFile = NULL){
        $arConfig = self::getConfig();
        
        $this->templatePath = CFile::normalizePath("/" . $arConfig["path"]);
        $this->layoutPath   = CFile::normalizePath($this->templatePath . "/" . $arConfig["layoutPath"]);
        $this->pagePath     = CFile::normalizePath($this->templatePath . "/" . $arConfig["pagePath"]);            
        $this->pageFile     = CFile::normalizePath($this->pagePath . "/" . $pageFile);
        $this->layoutFile   = CFile::normalizePath($this->layoutPath . "/" . $layoutFile);
        
        CEvent::trigger("CORE.TEMPLATE.START", array($this));
        
        /*render page*/
        if(is_file(ROOT_PATH . $this->pageFile)){
            $obView             = new CView();
            $obView->template   = $this;
            $this->content      = $obView->getContent(ROOT_PATH . $this->pageFile);
        }else{
            CEvent::trigger("CORE.TEMPLATE.PAGE.NOT_FOUND", array($this));
        }
        /*render page*/
        
        /*render layout*/            
        if(is_file(ROOT_PATH . $this->layoutFile)){
            $obView             = new CView();
            $obView->template   = $this;                
            $obView->content    = $this->content;                
            $this->content      = $obView->getContent(ROOT_PATH . $this->layoutFile);
        }else{
            CEvent::trigger("CORE.TEMPLATE.LAYOUT.NOT_FOUND", array($this));
        }
        /*render layout*/

        return $this->content;
    }
}
?>