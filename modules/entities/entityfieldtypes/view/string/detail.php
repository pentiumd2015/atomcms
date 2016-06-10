<?
use \Entities\EntityFieldTypes\FieldTypeString;
use \Helpers\CHtml;

$obField    = $obFieldType->obEntityField;
$arParams   = $obFieldType->obEntityField->params;

if($obField->is_multi){
    ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?=$obField->title;?>:<?=($obField->is_required ? '<span class="mandatory">*</span>' : "");?></label>
            <div class="col-sm-6">
                <div class="item_field_values_container_<?=$containerID;?>">
                    <?
                        if(is_array($arValues)){
                            $i = 0;
                            foreach($arValues AS $valueID => $value){
                                if($valueID == "n"){
                                    $arValue = $value;
                                    foreach($arValue AS $value){
                                        ?>
                                            <div class="row item_field_value<?=($arErrors ? " has-error" : "")?>">
                                                <div class="col-sm-9">
                                                    <?=CHtml::text("entity_item[" . $obField->entity_field_id . "][n][]", $value, array(
                                                        "class" => "form-control",
                                                    ));?>
                                                </div>
                                                <?
                                                    if($i > 0){
                                                        ?>
                                                            <div class="col-sm-3">
                                                                <a href="#" class="btn btn-icon btn-primary btn-xs item_field_value_remove">
                                                                    <i class="icon icon-close"></i>
                                                                </a>
                                                            </div>
                                                        <?
                                                    }
                                                ?>
                                            </div>
                                        <?
                                    }
                                }else{
                                    $value = (is_array($value) ? $value[FieldTypeString::VALUE_NAME] : $value);                                    
                                    ?>
                                        <div class="row item_field_value<?=($arErrors ? " has-error" : "")?>">
                                            <div class="col-sm-9">
                                                <?=CHtml::text("entity_item[" . $obField->entity_field_id . "][" . $valueID . "]", $value, array(
                                                    "class" => "form-control",
                                                ));?>
                                            </div>
                                            <?
                                                if($i > 0){
                                                    ?>
                                                        <div class="col-sm-3">
                                                            <a href="#" class="btn btn-icon btn-primary btn-xs item_field_value_remove">
                                                                <i class="icon icon-close"></i>
                                                            </a>
                                                        </div>
                                                    <?
                                                }
                                            ?>
                                        </div>
                                    <?
                                }
                                
                                $i++;
                            }
                        }else{
                            ?>
                                <div class="row item_field_value<?=($arErrors ? " has-error" : "")?>">
                                    <div class="col-sm-9">
                                        <?=CHtml::text("entity_item[" . $obField->entity_field_id . "][n][]", "", array(
                                            "class" => "form-control",
                                        ));?>
                                    </div>
                                </div>
                            <?
                        }
                    ?>
                </div>
                <?
                    if($obField->description){
                        ?>
                            <span class="help-block"><?=$obField->description;?></span>
                        <?
                    }
                ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">&nbsp;</div>
            <div class="col-sm-10">
                <button class="btn btn-primary add_new_item_field_value_<?=$containerID;?>" type="button"><i class="icon icon-plus"></i> Добавить</button>
            </div>
        </div>
        <script type="template/html" class="item_field_value_template_<?=$containerID;?>">
            <div class="row item_field_value">
                <div class="col-sm-9">
                    <?=CHtml::text("entity_item[" . $obField->entity_field_id . "][n][]", "", array(
                        "class" => "form-control",
                    ));?>
                </div>
                <div class="col-sm-3">
                    <a href="#" class="btn btn-icon btn-primary btn-xs item_field_value_remove">
                        <i class="icon icon-close"></i>
                    </a>
                </div>
            </div>
        </script>
        <script type="text/javascript">
        $(function(){
            $(document).on("click", ".add_new_item_field_value_<?=$containerID;?>", function(){
                var newItem = getTemplate(".item_field_value_template_<?=$containerID;?>", {});
                
                $(".item_field_values_container_<?=$containerID;?>").append(newItem);
            });
                                            
            $(document).on("click", ".item_field_values_container_<?=$containerID;?> .item_field_value_remove", function(e){
                e.preventDefault();
                $(this).closest('.item_field_value').remove();
            });
        });
        </script>
        <style>
        .item_field_values_container_<?=$containerID;?> .item_field_value + .item_field_value{
            margin-top: 15px;
        }
        </style>
    <?
}else{
    $value      = "";
    $valueID    = "n";
    
    if(is_array($arValues)){
        $value  = reset($arValues);
        $value  = is_array($value) ? $value[FieldTypeString::VALUE_NAME] : $value ;
        $valueID= key($arValues);
    }
    ?>
        <div class="form-group<?=($arErrors ? " has-error" : "")?>">
            <label class="col-sm-2 control-label"><?=$obField->title;?>:<?=($obField->is_required ? '<span class="mandatory">*</span>' : "");?></label>
            <div class="col-sm-6">
                <div class="row item_field_value<?=($arErrors ? " has-error" : "")?>">
                    <div class="col-sm-12">
                        <?=CHtml::text("entity_item[" . $obField->entity_field_id . "][" . $valueID . "]", $value, array(
                            "class" => "form-control",
                        ));?>
                    </div>
                </div>
                <?
                    if($obField->description){
                        ?>
                            <span class="help-block"><?=$obField->description;?></span>
                        <?
                    }
                ?>
            </div>
        </div>
    <?
}
?>