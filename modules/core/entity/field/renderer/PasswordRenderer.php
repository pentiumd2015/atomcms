<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CBuffer;

class PasswordRenderer extends StringRenderer{
    public function renderDetail($value, array $arData = [], array $arOptions = []){
        $obField        = $this->getField();
        $fieldName      = $obField->getName();
        $arParams       = $this->getParams();

        if($obField->disabled){
            return "<div class=\"control-disabled\">" . (is_array($value) ? reset($value) : $value) . "</div>";
        }
        
        CBuffer::start();
            if($obField->multi){
                $containerID = uniqid($fieldName . "_");
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                            <div class="col-sm-6 control-content">
                                <div class="entity_field_container_<?=$containerID;?>">
                                    <?
                                        $arValues = is_array($value) ? $value : [$value];
                                    
                                        $i = 0;
                                        
                                        foreach($arValues AS $value){
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <?
                                                            echo CHtml::password($arParams["requestName"] . "[" . $fieldName . "][]", $value, [
                                                                "class" => "form-control"
                                                            ]);
                                                            
                                                            if($i == 0 && $obField->description){
                                                                ?>
                                                                    <span class="help-block"><?=$obField->description;?></span>
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
                                    <?=CHtml::password($arParams["requestName"] . "[" . $fieldName . "][]", "", [
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
                        <label class="col-sm-2 control-label"><?=$obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?
                                        echo CHtml::password($arParams["requestName"] . "[" . $fieldName . "]", $value, [
                                            "class" => "form-control"
                                        ]);
                                    
                                        if($obField->description){
                                            ?>
                                                <span class="help-block"><?=$obField->description;?></span>
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
?>