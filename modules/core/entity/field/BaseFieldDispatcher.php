<?
namespace Entity\Field;

use Entity\Query;

abstract class BaseFieldDispatcher{
    protected $query;
    
    protected $fields = [];
    
    abstract public function add($result);
    abstract public function update($id, $result);
    abstract public function delete($id, $result);
    
    
    public function __construct(Query $query){
        $this->query = $query;
    }
    
    public function getQuery(){
        return $this->query;
    }
    
    public function fetch($result){
        $this->onFetch($result);
    }
    
    protected function onBeforeAdd($result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onBeforeAdd($value, $result);
                
                if(is_callable($field->onSaveData)){
                    $field->onSaveData($value, $result, $field);   
                }
            }
        }
        
        return $this;
    }
    
    protected function onAfterAdd($result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onAfterAdd($value, $result);
            }
        }
        
        return $this;
    }
    
    protected function onBeforeUpdate($id, $result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onBeforeUpdate($value, $result);
                
                if(is_callable($field->onSaveData)){
                    $field->onSaveData($value, $result, $field);   
                }
            }
        }
    }
    
    protected function onAfterUpdate($id, $result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onAfterUpdate($value, $result);
            }
        }
    }
    
    protected function onBeforeDelete($id, $result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onBeforeDelete($value, $result);
            }
        }
    }
    
    protected function onAfterDelete($id, $result){
        foreach($result->getData() AS $fieldName => $value){
            if($field = $this->getField($fieldName)){
                $field->onAfterDelete($value, $result);
            }
        }
    }
    
    protected function onFetch($result){
        foreach($result->getSelectFieldNames() AS $fieldName){
            if($field = $this->getField($fieldName)){
                $field->onFetch($result);
                
                if(is_callable($field->onFetchData)){
                    $field->onFetchData($result, $field);
                }
            }
        }
    }
    
    public function addField($field){
        if($this->isField($field)){
            $field->setDispatcher($this);
            $this->fields[$field->getName()] = $field;
        }
        
        return $this;
    }
    
    public function isField($field){
        return $field instanceof BaseField;
    }
    
    public function setFields(array $fields = []){
        $this->fields = [];
        
        foreach($fields AS $fieldName => $field){
            $this->addField($field);
        }
        
        return $this;
    }
    
    public function getFields(){
        return $this->fields;
    }
    
    public function getField($fieldName){
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : false ;
    }
}