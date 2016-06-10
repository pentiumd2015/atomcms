<?
namespace Entities\EntityFieldTypes;

class FieldType{
    protected $arParams         = array();
    protected $arData           = array();
    protected $arPrepareData    = array();
    
    public function getInfo(){
        return array();
    }
    
    public function getSafeParams($arParams, $arSafeParams){
        $arSafeParams = array();
        
        foreach($arSafeParams AS $safeParam){
            $arSafeParams[$safeParam] = 1;
        }
        
        foreach($arParams AS $param => $value){
            if(!isset($arSafeParams[$param])){
                unset($arParams[$param]);
                continue;
            }
            
            if(!strlen($arParams[$param])){
                $arParams[$param] = NULL;
            }
        }
        
        return $arParams;
    }
    
    public function setParams($arParams){
        $this->arParams = $arParams;
        
        return $this;
    }
    
    public function getParams(){
        return $this->arParams;
    }
    
    public function setData($arData){
        $this->arData = $arData;
        
        return $this;
    }
    
    public function getData(){
        return $this->arData;
    }
    
    public function prepareListData($arData = array()){}
    public function renderList(){}
    public function prepareDetailData($arData = array()){}
    public function renderDetail(){}
    public function renderFilter(){}
    public function getSqlParams($arSqlParams = array(), $arParams = array(), $type){
        return $arSqlParams;
    }
}
?>