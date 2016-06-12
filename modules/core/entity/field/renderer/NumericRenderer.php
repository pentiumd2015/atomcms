<?
namespace Entity\Field\Renderer;

use \Helpers\CHtml;
use \Helpers\CBuffer;

class NumericRenderer extends StringRenderer{
    public function renderFilter($value, array $arData = [], array $arOptions = []){
        $obField        = $this->getField();
        $fieldName      = $obField->getName();
        $arParams       = $this->getParams();

        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$obField->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($arParams["requestName"] . "[" . $fieldName . "]", $value, [
                            "class" => "form-control input-sm"
                        ]);?>
                        <span class="help-block">пример: 15 ; 1-69 ; 1;56 ; -54 ; 126-</span>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderParams(){
        
    }
}
?>