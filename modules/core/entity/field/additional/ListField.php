<?
namespace Entity\Field\Additional;

use \Helpers\CArrayHelper;

class ListField extends AdditionalField{
    protected $arInfo = array(
        "title" => "Список значений"
    );
    
    public function getColumnName(){
        return "value_enum";
    }
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\ListRenderer($this);
    }
    
    public function loadValues(){
        /*load at once*/
        static $arValues = NULL;
        
        if($arValues == NULL){
            $arFieldParams = $this->getParams();
            
            if(is_callable($arFieldParams["values"])){
                $valuesHandler = $arFieldParams["values"];
                
                $arValues = $valuesHandler();
                $arValues = CArrayHelper::index($arValues, "id");
            }else if(is_array($arFieldParams["values"])){
                $arValues = $arFieldParams["values"];
            }else{
                $arValues = array(1 => "some");
                /*
                $arFieldVariants = EntityFieldVariant::findAll(array(
                    "condition" => "entity_field_id=?",
                    "order"     => "priority ASC"
                ), array($this->obEntityField->entity_field_id));*/
            }
        }
        /*load at once*/
        
        return $arValues;
    }
}
?>