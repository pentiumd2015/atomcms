<?
namespace Entity\Result;

class BaseResult{
    protected $arErrors = array();
    protected $success;
    protected $arData = [];
    
    public function setSuccess($isSuccess){
        $this->success = $isSuccess;
        
        return $this;
    }
    
    public function isSuccess(){
        return $this->success;
    }
    
    public function setErrors($arErrors){
        $this->success  = false;
        $this->arErrors = $arErrors;
        
        return $this;
    }
    
    public function getErrors(){
        return $this->arErrors;
    }
    
    public function setData($arData){
        $this->arData = $arData;
        
        return $this;
    }
    
    public function getData(){
        return $this->arData;
    }
}
?>