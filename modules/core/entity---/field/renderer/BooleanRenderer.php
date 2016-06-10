<?
namespace Entity\Field\Renderer;

use \CHtml;

class BooleanRenderer extends Renderer{
    public function renderList($value, $arRow, $arParams = array()){
        $arVariants = $this->getField()->loadValues();
        
        return "<span class=\"label label-" . ($value ? "success" : "warning") . "\">" . $arVariants[$value] . "</span>";
    }
    
    public function renderFilter($value, $arData, $arParams = array()){
        $value = (string)$value;
        
        $obField        = $this->getField();
        $arVariants     = $obField->loadValues();
        $arFieldParams  = $obField->getParams();
        $fieldName      = $obField->getFieldName();
        
        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-3 control-label\" for=\"list_filter_active\">" . $arFieldParams["title"] . ":</label>
                    <div class=\"col-sm-9\">";

        if(count($arVariants)){
            foreach($arVariants AS $val => $title){
                $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                            CHtml::radio($arParams["requestArrayName"] . "[" . $fieldName . "]", ($val == $value), array(
                                "id"    => $arParams["filterID"] . "_" . $fieldName . "_" . $val,
                                "value" => $val
                            )) .
                            "<label for=\"" . ($arParams["filterID"] . "_" . $fieldName . "_" . $val) . "\">" . $title . "</label>
                        </div>";
            }
        }         
                        
        $str.= "<div class=\"radio radio-inline radio-primary\">" . 
                    CHtml::radio($arParams["requestArrayName"] . "[" . $fieldName . "]", (!strlen($value)), array(
                        "id"    => $arParams["filterID"] . "_" . $fieldName,
                        "value" => ""
                    )) . 
                    "<label for=\"" . $arParams["filterID"] . "_" . $fieldName . "\">Не важно</label>
                </div>
            </div>
        </div>";
        
        return $str;
    }
    
    public function renderDetail($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arVariants     = $obField->loadValues();
        $arFieldParams  = $obField->getParams();
        $fieldName      = $obField->getFieldName();
        
        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-2 control-label\">" . $arFieldParams["title"] . ":" . ($arFieldParams["required"] ? "<span class=\"mandatory\">*</span>" : "") . "</label>
                    <div class=\"col-sm-6 control-content\">
                        <div class=\"checkbox checkbox-primary\">" . 
                            CHtml::boolean($arParams["requestArrayName"] . "[" . $fieldName . "]", array_keys($arVariants), $value, array(
                                "id" => $fieldName . "_is_active"
                            )) . 
                            "<label for=\"" . $fieldName . "_is_active\"></label>
                        </div>
                    </div>
                </div>";
                
        return $str;
    }
}
?>