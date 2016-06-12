<?
class CAutoload{
    protected static $obj       = null;
    protected $autoloadDirMap   = [];
    protected $autoLoadClassMap = [];
    protected $rootPath         = null;
    
    public static function getInstance(){
        if(static::$obj == null){
            static::$obj = new static;
        }
        
        return static::$obj;
    }
    
    public function __construct(){
        spl_autoload_extensions(".php");
        spl_autoload_register([$this, "load"]);
    }
    
    public function setRootPath($rootPath = null){
        $this->rootPath = $rootPath;
        
        return $this;
    }
    
    public function getRootPath(){
		return $this->rootPath ? $this->rootPath : $_SERVER["DOCUMENT_ROOT"];
    }
    
    protected function load($className){
        $classPath  = $this->preparePath($className);
        $rootPath   = $this->getRootPath();
        
        if(isset($this->autoLoadClassMap[$classPath])){
            $file = $this->autoLoadClassMap[$classPath];
        
            if(is_file($rootPath . "/" . $file)){
                require_once($rootPath . "/" . $file);
                return;
            }else{
                foreach($this->autoloadDirMap AS $dirPath => $loaded){
                    $classFile = $rootPath . "/" . $dirPath . "/" . $file;
                    
                    if(is_file($classFile)){
                        require_once($classFile);
                        return;
                    }
                }
            }
        }
        
        foreach($this->autoloadDirMap AS $dirPath => $loaded){
            $classFile = $rootPath . "/" . $dirPath . "/" . $classPath . ".php";

            if(is_file($classFile)){
                require_once($classFile);
                return;
            }
        }
        
        exit;
        
        return false;
    }
    
    public function addDirMap($dirPath){ //add dir for autoload classes
        $dirPaths = is_array($dirPath) ? $dirPath : [$dirPath] ;
        
        foreach($dirPaths AS $dirPath){
            $this->autoloadDirMap[$this->normalizePath($dirPath)] = true;
        }
    }
    
    public function addClassMap(array $classMap){
		foreach($classMap AS $class => $classPath){
			$this->autoLoadClassMap[$this->preparePath($class)] = $this->normalizePath($classPath);
		}
	}
    
    protected function normalizePath($path){
        $path = str_replace("\\", "/", $path);
        return ltrim($path, "/");
    }
    
    protected function preparePath($classPath){
        $classPath = $this->normalizePath($classPath);

        if(($lastPos = strripos($classPath, "/")) !== false){
            $classPath = strtolower(substr($classPath, 0, $lastPos)) . substr($classPath, $lastPos);
        }
        
        return $classPath;
    }
}
?>