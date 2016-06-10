<?
use \Entities\EntityFieldTypes\FieldTypeList;
use \Helpers\CHtml;
use \Helpers\CArrayHelper;

$obField    = $obFieldType->obEntityField;
$arParams   = $obFieldType->obEntityField->params;

if($obField->is_multi){
    switch($arParams["view"]){
        case "multiselect":
            $arVariantsOptionList = CArrayHelper::getKeyValue($arFieldVariants, "entity_field_variant_id", "title");
            
            $value = array();
            
            if(is_array($arValues)){
                foreach($arValues AS $arValue){
                    $value[] = is_array($arValue) ? $arValue[FieldTypeList::VALUE_NAME] : $arValue;
                }
            }
            ?>
                <div class="form-group<?=($arErrors ? " has-error" : "")?>">
                    <label class="col-sm-2 control-label"><?=$obField->title;?>: </label>
                    <div class="col-sm-6">
                        <?=CHtml::multiselect("entity_item[" . $obField->entity_field_id . "]", $arVariantsOptionList, $value, array(
                            "class" => "form-control item_field_value_" . $containerID,
                        ));?>
                    </div>
                </div>
                <style>
                select.item_field_value_<?=$containerID;?> option {
                    padding: 5px;
                }
                </style>
            <?
            break;
        case "checkbox":
            if(count($arFieldVariants)){
                $value      = array();
                
                if(is_array($arValues)){
                    foreach($arValues AS $arValue){
                        $val = is_array($arValue) ? $arValue[FieldTypeList::VALUE_NAME] : $arValue;
                        $value[$val] = 1;
                    }
                }
                ?>
                    <div class="form-group<?=($arErrors ? " has-error" : "")?>">
                        <label class="col-sm-2 control-label"><?=$obField->title;?>: </label>
                        <div class="col-sm-6">
                            <?
                                echo CHtml::hidden("entity_item[" . $obField->entity_field_id . "][]", "");
                            
                                foreach($arFieldVariants AS $obFieldVariant){
                                    ?>
                                        <div class="checkbox checkbox-primary">
                                            <?=CHtml::checkbox("entity_item[" . $obField->entity_field_id . "][]", isset($value[$obFieldVariant->entity_field_variant_id]), array(
                                                "id"        => "item_field_list_" . $obFieldVariant->entity_field_variant_id,
                                                "value"     => $obFieldVariant->entity_field_variant_id
                                            ));?>
                                            <label for="item_field_list_<?=$obFieldVariant->entity_field_variant_id;?>"><?=$obFieldVariant->title;?></label>
                                        </div>
                                    <?
                                }
                            ?>
                        </div>
                    </div>
                <?
            }
            break;
    } 
}else{
    switch($arParams["view"]){
        case "select":
            $arVariantsOptionList = CArrayHelper::getKeyValue($arFieldVariants, "entity_field_variant_id", "title");

            $value      = "";
            $valueID    = "n";
            
            if(is_array($arValues)){
                $value  = reset($arValues);
                $value  = is_array($value) ? $value[FieldTypeList::VALUE_NAME] : $value ;
                $valueID= key($arValues);
            }
            ?>
                <div class="form-group<?=($arErrors ? " has-error" : "")?>">
                    <label class="col-sm-2 control-label"><?=$obField->title;?>: </label>
                    <div class="col-sm-6">
                        <?=CHtml::select("entity_item[" . $obField->entity_field_id . "][" . $valueID . "]", $arVariantsOptionList, $value, array(
                            "class" => "form-control item_field_value_" . $containerID,
                        ));?>
                    </div>
                </div>
                <script type="text/javascript">
                $(function(){
                    $(".item_field_value_<?=$containerID;?>").select({
                        inputEnable: true,
                        markMatch: true
                    });
                });
                </script>
            <?
            break;
        case "radio":
            if(is_array($arFieldVariants)){
                $value      = "";
                $valueID    = "n";
                
                if(is_array($arValues)){
                    $value  = reset($arValues);
                    $value  = is_array($value) ? $value[FieldTypeList::VALUE_NAME] : $value ;
                    $valueID= key($arValues);
                }
                ?>
                    <div class="form-group<?=($arErrors ? " has-error" : "")?>">
                        <label class="col-sm-2 control-label"><?=$obField->title;?>: </label>
                        <div class="col-sm-6">
                            <?
                                foreach($arFieldVariants AS $obFieldVariant){
                                    ?>
                                        <div class="radio radio-primary">
                                            <?=CHtml::radio("entity_item[" . $obField->entity_field_id . "][" . $valueID . "]", ($obFieldVariant->entity_field_variant_id == $value), array(
                                                "id"        => "item_field_list_" . $obFieldVariant->entity_field_variant_id,
                                                "value"     => $obFieldVariant->entity_field_variant_id
                                            ));?>
                                            <label for="item_field_list_<?=$obFieldVariant->entity_field_variant_id;?>"><?=$obFieldVariant->title;?></label>
                                        </div>
                                    <?
                                }
                            ?>
                        </div>
                    </div>
                <?
            }
            break;
    }
}
?>