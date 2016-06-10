<?
namespace Entity\Result;

class SelectResult extends BaseResult{
    public function setSelectFieldNames(array $arSelectFieldNames = []){
        $this->arSelectFieldNames = $arSelectFieldNames;
        
        return $this;
    }
    
    public function getSelectFieldNames(){
        return $this->arSelectFieldNames;
    }
}