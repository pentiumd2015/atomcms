<?
namespace Entity\Field\Renderer;

use Helpers\CHtml;
use Helpers\CBuffer;

class PasswordRenderer extends StringRenderer{
    public function renderDetail($value, array $data = [], array $arOptions = []){
        $field      = $this->getField();
        $fieldName  = $field->getName();

        if($field->disabled){
            return "<div class=\"control-disabled\">" . (is_array($value) ? reset($value) : $value) . "</div>";
        }
        
        CBuffer::start();
            if($field->multi){
                $containerID = uniqid($fieldName . "_");
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$field->title . ":" . ($field->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                            <div class="col-sm-6 control-content">
                                <div class="entity_field_container_<?=$containerID;?>">
                                    <?
                                        $values = is_array($value) ? $value : [$value];
                                    
                                        $i = 0;
                                        
                                        foreach($values AS $value){
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <?
                                                            echo CHtml::password($options["requestName"] . "[" . $fieldName . "][]", $value, [
                                                                "class" => "form-control"
                                                            ]);
                                                            
                                                            if($i == 0 && $field->description){
                                                                ?>
                                                                    <span class="help-block"><?=$field->description;?></span>
                                                                <?
                                                            }
                                                        ?>
                                                    </div>
                                                    <?
                                                        if($i > 0){
                                                            ?>
                                                                <div class="col-sm-3">
                                                                    <?=CHtml::button("<i class=\"icon icon-close\"></i>", [
                                                                        "class"     => "btn btn-icon btn-primary btn-xs",
                                                                        "onclick"   => "$(this).closest(\".row\").remove();"
                                                                    ]);?>
                                                                </div>
                                                            <?
                                                        }
                                                    ?>
                                                </div>
                                            <?
                                            
                                            $i++;
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <?=CHtml::button("<i class=\"icon icon-plus\"></i> Добавить", [
                                    "class"     => "btn btn-primary",
                                    "onclick"   => "addValue" . $containerID . "(this);"
                                ]);?>
                            </div>
                        </div>
                        <script type="template/html" id="entity_field_value_template_<?=$containerID;?>">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?=CHtml::password($options["requestName"] . "[" . $fieldName . "][]", "", [
                                        "class" => "form-control",
                                    ]);?>
                                </div>
                                <div class="col-sm-3">
                                    <?=CHtml::button("<i class=\"icon icon-close\"></i>", [
                                        "class"     => "btn btn-icon btn-primary btn-xs",
                                        "onclick"   => "$(this).closest(\".row\").remove();"
                                    ]);?>
                                </div>
                            </div>
                        </script>
                        <script type="text/javascript">
                            function addValue<?=$containerID;?>(el){
                                var newItem = AdminTools.getTemplate("#entity_field_value_template_<?=$containerID;?>", {});
                                $(".entity_field_container_<?=$containerID;?>").append(newItem);
                            }
                        </script>
                        <style>
                        .entity_field_container_<?=$containerID;?> .row + .row{
                            margin-top: 15px;
                        }
                        </style>
                    <?
            }else{
                $value = is_array($value) ? reset($value) : $value ;
                ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?=$field->title . ":" . ($field->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?
                                        echo CHtml::password($options["requestName"] . "[" . $fieldName . "]", $value, [
                                            "class" => "form-control"
                                        ]);
                                    
                                        if($field->description){
                                            ?>
                                                <span class="help-block"><?=$field->description;?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?
            }
        
        return CBuffer::end();
    }
}