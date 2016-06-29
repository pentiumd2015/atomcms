<?
namespace Entity\Field\Scalar;

use DB\Query AS DbQuery;
use Entity\Field\BaseFieldDispatcher;

class FieldDispatcher extends BaseFieldDispatcher{
    public function isField($field){
        return $field instanceof Field;
    }
    
    public function add($result){
        $this->onBeforeAdd($result);

        $data = $result->getData();

        $fieldValues = [];

        foreach($this->fields AS $fieldName => $field){
            if(isset($data[$fieldName])){
                $fieldValues[$fieldName] = $data[$fieldName];
            }
        }
        
        if(!count($fieldValues)){
            return false;
        }

        $id = (new DbQuery)->from($this->query->from)
                           ->insert($fieldValues);

        if($id){
            $result->setDataValues([
                $this->query->getManager()->getPk() => $id
            ]);
            
            $this->onAfterAdd($result);
            
            return true;
        }else{
            return false;
        }
    }
    
    public function update($id, $result){
        $this->onBeforeUpdate($id, $result);

        $data = $result->getData();

        $fieldValues = [];

        foreach($this->fields AS $fieldName => $field){
            if(isset($data[$fieldName])){
                $fieldValues[$fieldName] = $data[$fieldName];
            }
        }

        if(count($fieldValues)){
            (new DbQuery)->from($this->query->from)
                         ->where($this->query->getManager()->getPk(), $id)
                         ->update($fieldValues);
                
            $this->onAfterUpdate($id, $result);
        }

        return true;
    }
    
    public function delete($id, $result){
        $this->onBeforeDelete($id, $result);
        
        (new DbQuery)->from($this->query->from)
                     ->where($this->query->getManager()->getPk(), $id)
                     ->delete();
        
        $this->onAfterDelete($id, $result);
        
        return true;
    }
}