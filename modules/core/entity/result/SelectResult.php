<?
namespace Entity\Result;

use DB\Manager\Result\BaseResult;

class SelectResult extends BaseResult{
    protected $selectFieldNames = [];
    
    public function setSelectFieldNames(array $selectFieldNames = []){
        $this->selectFieldNames = $selectFieldNames;
        
        return $this;
    }
    
    public function getSelectFieldNames(){
        return $this->selectFieldNames;
    }
}