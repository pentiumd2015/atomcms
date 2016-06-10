<?
namespace Entity\Result;

class Result{
    protected $arErrors = array();
    protected $success;
    
    public function setSuccess($isSuccess){
        $this->success = $isSuccess;
    }
    
    public function isSuccess(){
        return $this->success;
    }
    
    public function setErrors($arErrors){
        $this->success  = false;
        $this->arErrors = $arErrors;
    }
    
    public function getErrors(){
        return $this->arErrors;
    }
    
}
?>