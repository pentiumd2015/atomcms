<?
class CAutoload{
    static protected $arAutoloadDirMap      = array();
    static protected $arAutoLoadClassMap    = array();
    
    static public function getRootPath(){
		return $_SERVER["DOCUMENT_ROOT"];
    }
    
    static public function init(){
        spl_autoload_extensions(".php");
        spl_autoload_register(array(__CLASS__, "load"));
    }
    
    static protected function load($className){
        $classPath  = self::preparePath($className);
        $rootPath   = self::getRootPath();
        
        if(isset(self::$arAutoLoadClassMap[$classPath])){
            $file = self::$arAutoLoadClassMap[$classPath];
        
            if(is_file($rootPath . "/" . $file)){
                require_once($rootPath . "/" . $file);
                return;
            }else{
                foreach(self::$arAutoloadDirMap AS $dirPath => $loaded){
                    $classFile = $rootPath . "/" . $dirPath . "/" . $file;
                    
                    if(is_file($classFile)){
                        require_once($classFile);
                        return;
                    }
                }
            }
        }

        foreach(self::$arAutoloadDirMap AS $dirPath => $loaded){
            $classFile = $rootPath . "/" . $dirPath . "/" . $classPath . ".php";

            if(is_file($classFile)){
                require_once($classFile);
                return;
            }
        }
        
        return false;
    }
    
    static public function addDirMap($dirPath){ //add dir for autoload classes
        $arDirs = is_array($dirPath) ? $dirPath : array($dirPath) ;
        
        foreach($arDirs AS $dirPath){
            $dirPath = str_replace("\\", "/", $dirPath);
            $dirPath = ltrim($dirPath, "/");
            
            if(!isset(self::$arAutoloadDirMap[$dirPath])){
                self::$arAutoloadDirMap[$dirPath] = true;
            }
        }
    }
    
    public static function addClassMap(array $arClasses){
		foreach($arClasses AS $class => $classPath){
            $classPath = str_replace("\\", "/", $classPath);
            $classPath = ltrim($classPath, "/");
			self::$arAutoLoadClassMap[self::preparePath($class)] = $classPath;
		}
	}
    
    static protected function preparePath($classPath){
        $classPath = str_replace(array("_", "\\"), "/", $classPath);
        $classPath = ltrim($classPath, "/");

        if(($lastPos = strripos($classPath, "/")) !== false){
            $classPath = strtolower(substr($classPath, 0, $lastPos)) . substr($classPath, $lastPos);
        }
        
        return $classPath;
    }
}
?>