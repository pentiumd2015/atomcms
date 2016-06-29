<?
namespace Entity\Field\Additional;

use Entity\AdditionalField;
use \Entity\Query;
use \Helpers\CArrayHelper;

class FieldDispatcher extends \Entity\Field\BaseFieldDispatcher{
    protected $additionalField;
    
    public function __construct(Query $query){
        parent::__construct($query);

        $this->additionalField = new AdditionalField($query->getManager());

        foreach($this->additionalField->createFields() AS $field){
            $this->addField($field);
        }
    }
    
    public function isField($field){
        return $field instanceof Field;
    }
    
    protected function onFetch($result){
        $data = $result->getData();
        
        if(!count($data)){
            return ;
        }
        
        $fields = [];
        
        foreach($result->getSelectFieldNames() AS $fieldName){
            if($field = $this->getField($fieldName)){
                $fields[$fieldName] = $field;
            }
        }

        if(count($fields)){
            $pk         = $this->query->getManager()->getPk();
            $items      = CArrayHelper::index($data, $pk);
            $itemIds    = CArrayHelper::getColumn($items, $pk);

            foreach($this->additionalField->getFieldValues($itemIds, array_keys($fields)) AS $fieldValue){
                $field      = $fields[$fieldValue["name"]];
                $fieldName  = $field->getAlias() ? $field->getAlias() : $field->getName();
                $itemId     = $fieldValue["item_id"];
                
                if(!isset($items[$itemId][$fieldName])){
                    $items[$itemId][$fieldName] = [];
                }
                
                $items[$itemId][$fieldName][$fieldValue["id"]] = $fieldValue[$field->getColumnName()];
            }
            
            $result->setData(array_values($items));
            
            foreach($fields AS $fieldName => $field){
                $field->onFetch($result);
                
                if(is_callable($field->onFetchData)){
                    $field->onFetchData($result, $field);
                }
            }
        }
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
        
        if(count($fieldValues)){
            $id = $data[$this->query->getManager()->getPk()];
            $this->additionalField->setFieldValues([$id], $fieldValues);
            $this->onAfterAdd($result);
        }
        
        return true;
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
            $this->additionalField->setFieldValues([$id], $fieldValues);
            $this->onAfterUpdate($id, $result);
        }
        
        return true;
    }
    
    public function delete($id, $result){
        $this->onBeforeDelete($id, $result);
        $this->additionalField->setFieldValues([$id], []);
        $this->onAfterDelete($id, $result);
        
        return true;
    }
}