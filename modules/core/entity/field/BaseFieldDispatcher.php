<?
namespace Entity\Field;

use \Entity\Builder;
use \Entity\Result\BaseResult;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Result\SelectResult;

abstract class BaseFieldDispatcher{
    protected $obBuilder;
    
    protected $arFields = [];
    
    abstract public function add(AddResult $obResult);
    abstract public function update($id, UpdateResult $obResult);
    abstract public function delete($id, DeleteResult $obResult);
    
    
    public function __construct(Builder $obBuilder){
        $this->obBuilder = $obBuilder;
    }
    
    public function getBuilder(){
        return $this->obBuilder;
    }
    
    public function fetch(SelectResult $obResult, $oneRow = false){
        $this->onFetch($obResult);
    }
    
    protected function onBeforeAdd(AddResult $obResult){
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onBeforeAdd($value, $obResult);
                
                if(is_callable($obField->onSaveData)){
                    $obField->onSaveData($value, $obResult, $obField);   
                }
            }
        }
        
        return $this;
    }
    
    protected function onAfterAdd(AddResult $obResult){
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onAfterAdd($value, $obResult);
            }
        }
        
        return $this;
    }
    
    protected function onBeforeUpdate($id, UpdateResult $obResult){
        foreach($obResult->getChangedData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onBeforeUpdate($value, $obResult);
                
                if(is_callable($obField->onSaveData)){
                    $obField->onSaveData($value, $obResult, $obField);   
                }
            }
        }
    }
    
    protected function onAfterUpdate($id, UpdateResult $obResult){
        foreach($obResult->getChangedData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onAfterUpdate($value, $obResult);
            }
        }
    }
    
    protected function onBeforeDelete($id, DeleteResult $obResult){
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onBeforeDelete($value, $obResult);
            }
        }
    }
    
    protected function onAfterDelete($id, DeleteResult $obResult){
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onAfterDelete($value, $obResult);
            }
        }
    }
    
    protected function onFetch(SelectResult $obResult){
        foreach($obResult->getSelectFieldNames() AS $fieldName){
            if($obField = $this->getField($fieldName)){
                $obField->onFetch($obResult);
                
                if(is_callable($obField->onFetchData)){
                    $obField->onFetchData($obResult, $obField);
                }
            }
        }
    }
    
    public function addField($obField){
        if($this->isField($obField)){
            $obField->setDispatcher($this);
            $this->arFields[$obField->getName()] = $obField;
        }
        
        return $this;
    }
    
    public function isField($obField){
        return $obField instanceof BaseField;
    }
    
    public function setFields(array $arFields = []){
        $this->arFields = [];
        
        foreach($arFields AS $fieldName => $obField){
            $this->addField($obField);
        }
        
        return $this;
    }
    
    public function getFields(){
        return $this->arFields;
    }
    
    public function getField($fieldName){
        return isset($this->arFields[$fieldName]) ? $this->arFields[$fieldName] : false ;
    }
}