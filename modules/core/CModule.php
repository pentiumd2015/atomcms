<?
class CModule extends \DB\Manager{
    static protected $_table        = "module";
    static protected $_pk           = "module_id";
    static protected $_arConfig     = array();
    static protected $_arModules    = array();
    static protected $_arError      = array();
    
    static public function setConfig($arConfig = array()){
        return self::$_arConfig = $arConfig;
    }
    
    static public function getConfig(){
        return self::$_arConfig;
    }
    
    static public function load($module){
        if(!is_array($module)){
            return self::_load($module);
        }else{
            $arModule = $module;
            
            foreach($arModule AS $module){
                if(!self::_load($module)){
                    return false;
                }
            }
            
            return true;
        }
    }

    static public function getModuleDir($module){
        $arConfig   = self::getConfig();
        $modulePath = str_replace(array("\\", "."), "/" , $module);
        
        if(is_dir(ROOT_PATH . $arConfig["path"] . $modulePath)){
            return $arConfig["path"] . $modulePath;
        }
        
        return false;
    }
    
    static public function getLoaded(){
        return self::$_arModules;
    }
    
    /**
     * если расширение  core.someext , то грузим как обычно файл /core/someext.php,
     * если файла нет, то грузим /core/someext/autoload.php
     * если задан путь core.* , то грузим во всех каталогах core/somedir/autoload.php
     */
    static protected function _load($module){
        if(self::isLoaded($module)){
            return true;
        }
        
        $arConfig   = self::getConfig();
        $modulePath = str_replace(array("\\", "."), "/" , $module);
        
        if(substr($modulePath, -2) == "/*"){ //если добавляем все расширения в директории
            $ext        = substr($modulePath, 0, -2);
            $moduleDir  = self::getModuleDir($ext);

            if($moduleDir){
                $obIterator = \Helpers\CFile::scanDirectory(ROOT_PATH . $moduleDir);
                
                foreach($obIterator AS $obFileinfo){
                    if($obFileinfo->isDot()){
                        continue;
                    }
                    
                    if($obFileinfo->isDir()){
                        $module      = $ext . "." . $obFileinfo->getFileName();
                        $modulePath  = $obFileinfo->getPathname() . "/" . $arConfig["autoloadFile"];
                        
                        if(is_file($modulePath)){
                            self::$_arModules[$module] = $arConfig["path"] . $ext . "/" . $obFileinfo->getFileName() . "/" . $arConfig["autoloadFile"];
                            
                            include($modulePath);
                        }else{
                            self::$_arError[$module] = array(
                                "ERROR_MESSAGE" => "File [" . $arConfig["autoloadFile"] . "] in module [" . $module . "]",
                                "PATH"          => $modulePath
                            );
                            
                            return false;
                        }
                    }
                }
            }else{
                self::$_arError[$module] = array(
                    "ERROR_MESSAGE" => "Module dir [" . $moduleDir . "] in module [" . $module . "] not found",
                    "PATH"          => $moduleDir
                );
            }
            
            return true;
        }else{
            $moduleDir = self::getModuleDir($module);
            
            if($moduleDir){
                $arFiles = array(
                    $moduleDir . ".php", //если загружаем файл
                    $moduleDir . "/" . $arConfig["autoloadFile"] //если грузим директорию, то подгружаем файл автолоада
                );
                
                foreach($arFiles AS $modPath){
                    if(is_file(ROOT_PATH . "/" . $modPath)){
                        self::$_arModules[$module] = $modPath;
        
                        include(ROOT_PATH . "/" . $modPath);
                        
                        return true;
                    }
                }
                
                self::$_arError[$module] = array(
                    "ERROR_MESSAGE" => "File [" . implode("] or [", $arFiles) . "] in module [" . $module . "]",
                    "PATH"          => $moduleDir . "/" . $arConfig["autoloadFile"]
                );
            }else{
                self::$_arError[$module] = array(
                    "ERROR_MESSAGE" => "Module dir [" . $moduleDir . "] in module [" . $module . "] not found",
                    "PATH"          => $moduleDir
                );
            }
            
            return false;
        }
    }
    
    static public function isLoaded($module){
        return isset(self::$_arModules[$module]);
    }
    
    static public function getErrors(){
        return self::$_arError;
    }
}
?>