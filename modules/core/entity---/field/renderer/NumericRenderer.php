<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CBuffer;

class NumericRenderer extends Renderer{
    public function renderList($value, $arRow, $arParams = array()){
        return $value;
    }
    
    public function renderFilter($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arFieldParams  = $obField->getParams();
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$arFieldParams["title"];?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($arParams["requestArrayName"] . "[" . $obField->getFieldName() . "]", $value, array(
                            "class" => "form-control input-sm"
                        ));?>
                        <span class="help-block">пример: 15 ; 1-69 ; 1;56 ; -54 ; 126-</span>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, $arValues, $arParams = array()){
        $obField        = $this->getField();
        $arFieldParams  = $obField->getParams();
        
        $str = "<div class=\"form-group\">
                    <label class=\"col-sm-2 control-label\">" . $arFieldParams["title"] . ":" . ($arFieldParams["required"] ? "<span class=\"mandatory\">*</span>" : "") . "</label>
                    <div class=\"col-sm-6 control-content\">";
              
        if($arFieldParams["disabled"]){
            $str.= "<b style=\"line-height: 24px;\">" . $value . "</b>";
        }else{
            $str.= CHtml::text($arParams["requestArrayName"] . "[" . $obField->getFieldName() . "]", $value, array(
                "class" => "form-control"
            ));
        }
        
        $str.=     "</div>
                </div>";
        
        return $str;
    }
}
?>