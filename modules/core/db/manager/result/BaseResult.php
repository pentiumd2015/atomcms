<?
namespace DB\Manager\Result;

class BaseResult{
    protected $errors = [];
    protected $success;
    protected $data = [];
    
    public function setSuccess($isSuccess){
        $this->success = $isSuccess;
        
        return $this;
    }
    
    public function isSuccess(){
        return $this->success;
    }
    
    public function setErrors(array $errors = []){
        $this->success  = false;
        $this->errors   = $errors;
        
        return $this;
    }
    
    public function getErrors(){
        return $this->errors;
    }
    
    public function setData($data){
        $this->data = $data;
        
        return $this;
    }
    
    public function getData(){
        return $this->data;
    }

    public function setDataValues(array $values = []){
        return $this->setData(array_merge($this->data, $values));
    }
    
    
}