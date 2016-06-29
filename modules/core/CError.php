<?

class CError{
    public $config = [];
    
    public function __construct(array $config = []){
        $this->config = $config;
    }
    
    public function init(){
        ini_set("error_reporting", $this->config["errorTypes"]);
        ini_set("display_errors", $this->config["displayErrors"]);
        ini_set("log_errors", $this->config["logErrors"]);
        ini_set("error_log", ROOT_PATH . $this->config["logFile"]);
        
        error_reporting($this->config["errorTypes"]);

        set_exception_handler([$this, "exception"]);
        
        register_shutdown_function([$this, "fatalError"]);
    }
    
    public function error($code, $message, $file, $line){
        if($this->config["errorTypes"] & $code){
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            
            array_shift($trace);
            
            foreach ($trace as $frame){
                if ($frame["function"] === "__toString"){
                    $this->exception($exception);
                    print_r($frame);
                    exit;
                }
            }
            
            throw $exception;
        }
        
        return false;
    }
    
    public function exception($exception){p($exception); exit;
        CEvent::trigger("CORE.EXCEPTION", [$exception]);
    }
    
    public function fatalError(){
        if(($error = error_get_last()) !== null && $error["type"] & $this->config["errorTypes"]){
            CEvent::trigger("CORE.SHUTDOWN", [$error]);
            exit;
        }
    }
}