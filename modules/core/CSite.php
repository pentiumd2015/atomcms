<?
class CSite{
    public $siteID;
    public $domains;
    public $active;
    
    static protected $filePath = "/config/site.php";
    
    public function __construct(array $arSite){
        foreach($arSite AS $property => $value){
            $this->{$property} = $value;
        }
    }
    
    static public function getList(){
        $arSites = array();
        
        if(is_file(ROOT_PATH . static::$filePath) && ($arTmpSites = include(ROOT_PATH . static::$filePath)) && is_array($arTmpSites)){
            $arSites = $arTmpSites;
            
            foreach($arSites AS $siteID => &$arSite){
                $arSite["site_id"] = $siteID;
            }
        }
        
        return $arSites;
    }
    
    static protected function saveToFile($arSites){
        $arrayString = \Helpers\CArrayHelper::export($arSites);

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
    
    static public function delete($siteID){
        $arSites = static::getList();
        
        if(!is_array($siteID)){
            $arSiteIDs = array($siteID);
        }else{
            $arSiteIDs = $siteID;
        }
        
        $arSiteRoutes = CRouter::getList();

        foreach($arSiteIDs AS $siteID){
            if($arSites[$siteID]){
                unset($arSites[$siteID]);
                
                if(is_array($arSiteRoutes[$siteID])){
                    $arRoutePaths = array();
                    
                    foreach($arSiteRoutes[$siteID] AS $arRoute){
                        $arRoutePaths[] = $arRoute["path"];
                    }
                    
                    CRouter::delete($siteID, $arRoutePaths);
                }
            }
        }
        
        static::saveToFile($arSites);
                
        //нужно удалить все роуты для этого сайта
        
        
        
        
        
        
        
        
        
        \CEvent::trigger("CORE.SITE.DELETE", array($siteID));
        
        return true;
    }
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function update($siteID, $arData){
        return static::_save($arData, $siteID);
    }
    
    static protected function _save($arData, $siteID = false){        
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        if(!$siteID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
            
            if(empty($arData["site_id"])){
                $arErrors["site_id"][] = "Укажите id";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название";
            }
            
            if(isset($arData["site_id"]) && empty($arData["site_id"])){
                $arErrors["site_id"][] = "Укажите id";
            }
        }
        
        if(!count($arErrors)){
            $arSites = static::getList();
            
            $arData["domains"]  = str_replace(array("\n", "\r"), ",", trim($arData["domains"]));
            $arDomains          = explode(",", $arData["domains"]);
            $arDomains          = \Helpers\CArrayFilter::filter($arDomains);
            $arDomains          = array_values($arDomains);
            
            if(count($arDomains) == 1){
                $arData["domains"] = $arDomains[0];
            }else{
                $arData["domains"] = $arDomains;
            }
                        
            if(empty($arData["domains"])){
                $arData["domains"] = "*";
            }
            
            if($siteID){
                if(!is_array($siteID)){
                    $arSiteIDs = array($siteID);
                }else{
                    $arSiteIDs = $siteID;
                }
                
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "domains",
                    "active"
                ));
                
                foreach($arSiteIDs AS $siteID){
                    foreach($arData AS $param => $value){
                        if($arSites[$siteID]){
                            $arSites[$siteID][$param] = $value;
                        }
                    }
                }
            }else{
                $siteID = $arData["site_id"];
                
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "domains",
                    "active"
                ));
                
                if(isset($arData["active"])){
                    $arData["active"] = ($arData["active"] == 1) ? 1 : 0 ;
                }else{
                    $arData["active"] = 0;
                }
                
                $arSites[$siteID] = $arData;
            }
            
            static::saveToFile($arSites);
            
            if($siteID){
                \CEvent::trigger("CORE.SITE.UPDATE", array($siteID, $arData));
            }else{
                \CEvent::trigger("CORE.SITE.ADD", array($siteID, $arData));
            }
            
            $arReturn["id"]     = $siteID;
            $arReturn["success"]= true;
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>