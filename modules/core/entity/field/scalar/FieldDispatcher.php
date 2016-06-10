<?
namespace Entity\Field\Scalar;

use \Db\Builder AS DbBuilder;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Result\SelectResult;
use \Entity\Field\BaseFieldDispatcher;

class FieldDispatcher extends BaseFieldDispatcher{
    public function isField($obField){
        return $obField instanceof Field;
    }
    
    public function add(AddResult $obResult){
        $this->onBeforeAdd($obResult);
        
        $arFieldValues = [];
        
        foreach($obResult->getData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $arFieldValues[$fieldName] = $value;
            }
        }
        
        if(!count($arFieldValues)){
            return false;
        }
        
        $id = $this->getBuilder()->insert($arFieldValues);
        
        if($id){
            $obResult->setDataValues([
                $this->getBuilder()->getEntity()->getPk() => $id
            ]);
            
            $this->onAfterAdd($obResult);
            
            return true;
        }else{
            return false;
        }
    }
    
    public function update($id, UpdateResult $obResult){
        $this->onBeforeUpdate($id, $obResult);
        
        $arFieldValues = [];
        
        foreach($obResult->getChangedData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $arFieldValues[$fieldName] = $value;
            }
        }
        
        if(count($arFieldValues)){
            (new DbBuilder)->from($this->getBuilder()->from)
                           ->where($this->getBuilder()->getEntity()->getPk(), $id)
                           ->update($arFieldValues);
                           
            $this->onAfterUpdate($id, $obResult);
        }

        return true;
    }
    
    public function delete($id, DeleteResult $obResult){
        $this->onBeforeDelete($id, $obResult);
        
        (new DbBuilder)->from($this->getBuilder()->from)
                       ->where($this->getBuilder()->getEntity()->getPk(), $id)
                       ->delete();
        
        $this->onAfterDelete($id, $obResult);
        
        return true;
    }
    
    public function fetch(SelectResult $obResult, $oneRow = false){
        if($oneRow){
            $arData = [$this->getBuilder()->operation("fetch")];
        }else{
            $arData = $this->getBuilder()->operation("fetchAll");
        }

        $obResult->setData($arData);
        
        parent::fetch($obResult, $oneRow);
    }
}