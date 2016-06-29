<?
namespace Entity\Field\Custom;


class FieldDispatcher extends \Entity\Field\BaseFieldDispatcher{
    public function isField($field){
        return $field instanceof Field;
    }
    
    public function add($result){
        $data = $result->getData();

        foreach($this->fields AS $fieldName => $field){
            if(isset($data[$fieldName])){
                $value = $data[$fieldName];

                $field->onBeforeAdd($value, $result);

                if(is_callable($field->onSaveData)){
                    $field->onSaveData($result, $field);
                }

                $field->add($result);
                $field->onAfterAdd($value, $result);
            }
        }
        
        return true;
    }
    
    public function update($id, $result){
        $data = $result->getData();

        foreach($this->fields AS $fieldName => $field){
            if(isset($data[$fieldName])){
                $value = $data[$fieldName];

                $field->onBeforeUpdate($value, $result);

                if(is_callable($field->onSaveData)){
                    $field->onSaveData($result, $field);
                }

                $field->update($id, $result);
                $field->onAfterUpdate($value, $result);
            }
        }
        
        return true;
    }
    
    public function delete($id, $result){
        foreach($this->fields AS $field){
            $field->onBeforeDelete($result);
            $field->delete($id, $result);
            $field->onAfterDelete($result);
        }
        
        return true;
    }
}