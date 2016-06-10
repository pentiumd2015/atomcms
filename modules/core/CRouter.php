<?
use \Helpers\CArrayHelper;
use \Helpers\CArraySorter;

class CRouter{
    static protected $filePath = "/config/route.php";
    
    static public function getList($siteID = false){
        if(is_file(ROOT_PATH . static::$filePath) && ($arTmpRoutes = include(ROOT_PATH . static::$filePath)) && is_array($arTmpRoutes)){            
            if($siteID && is_array($arTmpRoutes[$siteID])){
                $arRoutes = $arTmpRoutes[$siteID];
                
                foreach($arRoutes AS $routePath => &$arRoute){
                    $arRoute["path"] = $routePath;
                }
                
                $arRoutes = CArraySorter::subSort($arRoutes, function($a, $b){
                    return strnatcasecmp($a["path"], $b["path"]);
                });
                
                return $arRoutes;
            }else{
                foreach($arTmpRoutes AS &$arRoutes){
                    foreach($arRoutes AS $routePath => &$arRoute){
                        $arRoute["path"] = $routePath;
                    }
                    
                    $arRoutes = CArraySorter::subSort($arRoutes, function($a, $b){
                        return strnatcasecmp($a["path"], $b["path"]);
                    });
                }
                
                return $arTmpRoutes;
            }
        }
        
        return array();
    }
    
    static protected function saveToFile($arRoutes){
        $arrayString = CArrayHelper::export($arRoutes);

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
    
    static public function delete($siteID, $routePath){
        $arRoutes = static::getList();
        
        if($arRoutes[$siteID]){
            if(!is_array($routePath)){
                $arRouteIDs = array($routePath);
            }else{
                $arRouteIDs = $routePath;
            }
            
            foreach($arRouteIDs AS $routePath){
                if($arRoutes[$siteID][$routePath]){
                    unset($arRoutes[$siteID][$routePath]);
                }
            }
            
            if(!count($arRoutes[$siteID])){
                unset($arRoutes[$siteID]);
            }
            
            static::saveToFile($arRoutes);
            
            \CEvent::trigger("CORE.ROUTE.DELETE", array($arRouteIDs));
            
            return true;
        }
        
        return false;
    }
    
    static public function add($siteID, $arData){
        return static::_save($siteID, $arData, false);
    }
    
    static public function update($siteID, $routePath, $arData){
        return static::_save($siteID, $arData, $routePath);
    }
    
    static protected function _save($siteID, $arData, $routePath = false){        
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
        
        $arSites = CSite::getList();
        
        if(!isset($arSites[$siteID])){
            $arErrors["site_id"][] = "Укажите id сайта";
        }
        
        if(!$routePath){
            if(empty($arData["path"])){
                $arErrors["path"][] = "Укажите путь";
            }
        }else{
            if(isset($arData["path"]) && empty($arData["path"])){
                $arErrors["path"][] = "Укажите путь";
            }
            
            if(isset($arData["site_id"]) && empty($arData["site_id"])){
                $arErrors["site_id"][] = "Укажите id сайта";
            }
        }
        
        $arRoutes = static::getList();
        
        if(!count($arErrors)){
            if($routePath){
                if(!is_array($routePath)){
                    $arRouteIDs = array($routePath);
                }else{
                    $arRouteIDs = $routePath;
                }
                
                if(is_array($arData["templates"])){
                    $arData["templates"] = array_values($arData["templates"]);
                }
                
                if(isset($arData["site_id"]) && $siteID != $arData["site_id"]){
                    $arRoutes[$arData["site_id"]] = $arRoutes[$siteID];
                    unset($arRoutes[$siteID]);
                }
                
                if(isset($arData["path"]) && $routePath != $arData["path"]){
                    $arRoutes[$siteID][$arData["path"]] = $arRoutes[$routePath];
                    unset($arRoutes[$siteID][$routePath]);
                    
                    $routePath = $arData["path"];
                }
                
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "active",
                    "alias",
                    "templates"
                ));
                
                if(is_array($arData["templates"])){
                    $arData["templates"] = array_values($arData["templates"]);
                }
                
                foreach($arRouteIDs AS $path){
                    foreach($arData AS $param => $value){
                        if($arRoutes[$siteID][$path]){
                            $arRoutes[$siteID][$path][$param] = $value;
                        }
                    }
                }
            }else{
                $routePath = $arData["path"];
                
                $arData = static::getSafeFields($arData, array(
                    "title",
                    "active",
                    "alias",
                    "templates"
                ));
                
                if(is_array($arData["templates"])){
                    $arData["templates"] = array_values($arData["templates"]);
                }
                
                $arRoutes[$siteID][$routePath] = $arData;
            }
            
            static::saveToFile($arRoutes);
            
            if($routePath){
                \CEvent::trigger("CORE.ROUTE.UPDATE", array($routePath, $arData));
            }else{
                \CEvent::trigger("CORE.ROUTE.ADD", array($routePath, $arData));
            }
            
            $arReturn["id"]     = $routePath;
            $arReturn["success"]= true;
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>