<?
use DB\Manager;

class CModule extends Manager{
    protected static $tableName     = "module";
    protected static $primaryKey    = "id";
    protected $config    = [];
    protected $modules   = [];
    protected $errors    = [];
    
    public function __construct(array $config = []){
        $this->config = $config;
    }
    
    public function setConfig(array $config = []){
        $this->config = $config;
        
        return $this;
    }
    
    public function getConfig(){
        return $this->config;
    }
    
    public function load($module){
        if(!is_array($module)){
            return $this->loadModule($module);
        }else{
            $modules = $module;
            foreach($modules AS $module){
                if(!$this->loadModule($module)){
                    return false;
                }
            }
            
            return true;
        }
    }
    
    /**
     * если расширение  core.someext , то грузим как обычно файл /core/someext.php,
     * если файла нет, то грузим /core/someext/autoload.php
     * если задан путь core.* , то грузим во всех каталогах core/somedir/autoload.php
     */
    protected function loadModule($module){
        if($this->isLoaded($module)){
            return true;
        }
        
        if(substr($module, -2) == ".*"){ //если добавляем все расширения в директории
            $parentModule = substr($module, 0, -2);

            if(($moduleDir = $this->getModuleDir($parentModule))){
                $obIterator = new \DirectoryIterator(ROOT_PATH . $moduleDir);
                
                foreach($obIterator AS $obFileinfo){
                    if($obFileinfo->isDot()){
                        continue;
                    }
                    
                    if($obFileinfo->isDir()){
                        $module         = $parentModule . "." . $obFileinfo->getFileName();
                        $autoloadFile   = $obFileinfo->getPathname() . "/" . $this->config["autoloadFile"];
                        
                        if(is_file($autoloadFile)){
                            $this->modules[$module] = $this->config["path"] . $parentModule . "/" . $obFileinfo->getFileName() . "/" . $this->config["autoloadFile"];
                            
                            include($autoloadFile);
                        }else{
                            $this->errors[$module] = [
                                "message"       => "File [" . $this->config["autoloadFile"] . "] not found in module [" . $module . "]",
                                "autoloadFile"  => $autoloadFile
                            ];
                            
                            return false;
                        }
                    }
                }
            }else{
                $this->errors[$module] = [
                    "message"       => "Module dir [" . $parentModule . "] in module [" . $module . "] not found",
                    "autoloadFile"  => $moduleDir
                ];
                
                return false;
            }
        }else if(($moduleDir = $this->getModuleDir($module))){
            $files = [
                $moduleDir . ".php", //если загружаем файл
                $moduleDir . "/" . $this->config["autoloadFile"] //если грузим директорию, то подгружаем файл автолоада
            ];
            
            foreach($files AS $moduleFile){
                if(is_file(ROOT_PATH . "/" . $moduleFile)){
                    include(ROOT_PATH . "/" . $moduleFile);
                    
                    $this->modules[$module] = $moduleFile;
                    
                    return true;
                }
            }
            
            $this->errors[$module] = [
                "message"       => "File [" . implode("] or [", $files) . "] in module [" . $module . "]",
                "autoloadFile"  => $moduleDir . "/" . $this->config["autoloadFile"]
            ];
        }else{
            $this->errors[$module] = [
                "message"       => "Module [" . $module . "] not found"
            ];
        }
    }

    public function getModuleDir($module){
        $modulePath = str_replace(["\\", "."], "/" , $module);

        if(is_dir(ROOT_PATH . $this->config["path"] . $modulePath)){
            return $this->config["path"] . $modulePath;
        }
        
        return false;
    }
    
    public function getLoaded(){
        return $this->modules;
    }
    
    public function isLoaded($module){
        return isset($this->modules[$module]);
    }
    
    public function getErrors(){
        return $this->errors;
    }
}