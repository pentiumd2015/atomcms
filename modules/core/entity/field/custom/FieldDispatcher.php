<?
namespace Entity\Field\Custom;

use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Result\SelectResult;

class FieldDispatcher extends \Entity\Field\BaseFieldDispatcher{
    public function isField($obField){
        return $obField instanceof Field;
    }
    
    public function add(AddResult $obResult){
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onBeforeAdd($obResult);
                
                if(is_callable($obField->onSaveData)){
                    $obField->onSaveData($obResult, $obField);
                }
                
                $obField->add($obResult);
                $obField->onAfterAdd($obResult);
            }
        }
        
        return true;
    }
    
    public function update($id, UpdateResult $obResult){
        foreach($obResult->getChangedData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $obField->onBeforeUpdate($id, $obResult);
                
                if(is_callable($obField->onSaveData)){
                    $obField->onSaveData($obResult, $obField);   
                }
                
                $obField->update($id, $obResult);
                $obField->onAfterUpdate($id, $obResult);
            }
        }
        
        return true;
    }
    
    public function delete($id, DeleteResult $obResult){
        foreach($this->arFields AS $obField){
            $obField->onBeforeDelete($id, $obResult);
            $obField->delete($id, $obResult);
            $obField->onAfterDelete($id, $obResult);
        }
        
        return true;
    }
}