<?
namespace Entity\Field\Renderer;

use Helpers\CHtml;
use Helpers\CBuffer;

class NumericRenderer extends StringRenderer{
    public function renderFilter($value, array $data = [], array $options = []){
        $field      = $this->getField();
        $fieldName  = $field->getName();

        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$field->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($options["requestName"] . "[" . $fieldName . "]", $value, [
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