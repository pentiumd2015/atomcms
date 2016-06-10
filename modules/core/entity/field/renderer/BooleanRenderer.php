<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CArrayHelper;

class BooleanRenderer extends ListRenderer{
    public function renderFilter($value, array $arData = [], array $arOptions = []){
        $value = is_array($value) ? reset($value) : $value ;
        
        $arValues   = $this->loadValues();
        $obField    = $this->getField();
        $fieldName  = $obField->getName();
        $arParams   = $this->getParams();

        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-3 control-label\" for=\"list_filter_active\">" . $obField->title . ":</label>
                    <div class=\"col-sm-9\">";

        if(count($arValues)){
            foreach($arValues AS $valueID => $arValue){
                $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                            CHtml::radio($arParams["requestName"] . "[" . $fieldName . "]", ($valueID == $value), array(
                                "id"    => $arParams["filterID"] . "_" . $fieldName . "_" . $valueID,
                                "value" => $valueID
                            )) .
                            "<label for=\"" . ($arParams["filterID"] . "_" . $fieldName . "_" . $valueID) . "\">" . $arValue["title"] . "</label>
                        </div>";
            }
        }         
                        
        $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                    CHtml::radio($arParams["requestName"] . "[" . $fieldName . "]", (!strlen($value)), array(
                        "id"    => $arParams["filterID"] . "_" . $fieldName,
                        "value" => ""
                    )) . 
                    "<label for=\"" . $arParams["filterID"] . "_" . $fieldName . "\">Не важно</label>
                </div>
            </div>
        </div>";
        
        return $str;
    }
    
    public function renderDetail($value, array $arData = [], array $arOptions = []){
        $arValues   = $this->loadValues();
        $obField    = $this->getField();
        $fieldName  = $obField->getName();
        $arParams   = $this->getParams();

        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-2 control-label\">" . $obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "") . "</label>
                    <div class=\"col-sm-6 control-content\">
                        <div class=\"checkbox checkbox-primary\">" . 
                            CHtml::boolean($arParams["requestName"] . "[" . $fieldName . "]", array_keys($arValues), $value, array(
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
?>