<?
namespace Entity\Field\Renderer;

use Helpers\CHtml;

class BooleanRenderer extends ListRenderer{
    public function renderFilter($value, array $data = [], array $options = []){
        $value = is_array($value) ? reset($value) : $value ;
        
        $values     = $this->loadValues();
        $obField    = $this->getField();
        $fieldName  = $obField->getName();

        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-3 control-label\" for=\"list_filter_active\">" . $obField->title . ":</label>
                    <div class=\"col-sm-9\">";

        if(count($values)){
            foreach($values AS $valueID => $valueItem){
                $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                            CHtml::radio($options["requestName"] . "[" . $fieldName . "]", ($valueID == $value), array(
                                "id"    => $options["filterId"] . "_" . $fieldName . "_" . $valueID,
                                "value" => $valueID
                            )) .
                            "<label for=\"" . ($options["filterId"] . "_" . $fieldName . "_" . $valueID) . "\">" . $valueItem["title"] . "</label>
                        </div>";
            }
        }         
                        
        $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                    CHtml::radio($options["requestName"] . "[" . $fieldName . "]", (!strlen($value)), array(
                        "id"    => $options["filterId"] . "_" . $fieldName,
                        "value" => ""
                    )) . 
                    "<label for=\"" . $options["filterId"] . "_" . $fieldName . "\">Не важно</label>
                </div>
            </div>
        </div>";
        
        return $str;
    }
    
    public function renderDetail($value, array $data = [], array $options = []){
        $values     = $this->loadValues();
        $obField    = $this->getField();
        $fieldName  = $obField->getName();

        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-2 control-label\">" . $obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "") . "</label>
                    <div class=\"col-sm-6 control-content\">
                        <div class=\"checkbox checkbox-primary\">" . 
                            CHtml::boolean($options["requestName"] . "[" . $fieldName . "]", array_keys($values), $value, array(
                                "id" => $fieldName . "_is_active"
                            )) . 
                            "<label for=\"" . $fieldName . "_is_active\"></label>
                        </div>
                    </div>
                </div>";
                
        return $str;
    }
    
    public function renderParams(){
        
    }
}