<?

class CAutoload{
    protected static $obj   = null;
    protected $dirMap       = [];
    protected $classMap     = [];
    protected $rootPath     = null;
    
    public static function getInstance(){
        if(static::$obj === null){
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
        if($this->rootPath === null){
            $this->rootPath = $_SERVER["DOCUMENT_ROOT"];
        }

		return $this->rootPath;
    }
    
    protected function load($className){
        $classPath  = $this->preparePath($className);
        $rootPath   = $this->getRootPath();

        if(isset($this->classMap[$classPath])){
            $file = $this->classMap[$classPath];
        
            if(is_file($rootPath . "/" . $file)){
                require_once($rootPath . "/" . $file);
                return;
            }

            foreach($this->dirMap AS $dirPath => $loaded){
                if(is_file($rootPath . "/" . $dirPath . "/" . $file)){
                    require_once($rootPath . "/" . $dirPath . "/" . $file);
                    return;
                }
            }
        }
        
        foreach($this->dirMap AS $dirPath => $loaded){
            if(is_file($rootPath . "/" . $dirPath . "/" . $classPath . ".php")){
                require_once($rootPath . "/" . $dirPath . "/" . $classPath . ".php");
                return;
            }
        }

        throw new CException("Class '" . $className . "' not found");
    }
    
    public function addDirMap($dirPath){ //add dir for autoload classes
        $dirPaths = is_array($dirPath) ? $dirPath : [$dirPath] ;
        
        foreach($dirPaths AS $dirPath){
            $this->dirMap[$this->normalizePath($dirPath)] = true;
        }
    }
    
    public function addClassMap(array $classMap){
		foreach($classMap AS $class => $classPath){
			$this->classMap[$this->preparePath($class)] = $this->normalizePath($classPath);
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