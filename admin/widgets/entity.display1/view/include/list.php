<?
use \Entities\EntityField;
use \Entities\EntityItem;
use \Entities\EntityAdminDisplay;
use \Helpers\CHtml;

$fieldIndex = 0;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?>, Настройка вида<small><?=EntityItem::$arTypes[$relation]["title"] . ". Список";?></small></h3>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <h5>Список доступных полей:</h5>
        <ul class="available_fields_list fields_list connected_sortable">
            <?
                $arChosenBaseFields     = array();
                $arChosenExtraFields    = array();
                
                foreach($arEntityDisplay AS $arField){
                    if($arField["isBase"]){
                        $arChosenBaseFields[$arField["field"]] = 1;
                    }else{
                        $arChosenExtraFields[$arField["field"]] = 1;
                    }
                }
                
                foreach($arBaseFields AS $fieldName => $arField){
                    if(isset($arChosenBaseFields[$fieldName])){
                        continue;
                    }
                    ?>
                        <li>
                            <div class="field_drag_handle"><i class="icon-move"></i></div>
                            (Осн. поле) <?=$arField["title"];?>
                            <?=CHtml::hidden("data[" . $fieldIndex . "][isBase]", 1);?>
                            <?=CHtml::hidden("data[" . $fieldIndex . "][field]", $fieldName);?>
                        </li>
                    <?
                    $fieldIndex++;
                }
            
                foreach($arEntityFields AS $fieldID => $obField){
                    if(isset($arChosenExtraFields[$fieldID])){
                        continue;
                    }
                    ?>
                        <li>
                            <div class="field_drag_handle"><i class="icon-move"></i></div>
                            (Доп. поле) <?=$obField->title;?>
                            <?=CHtml::hidden("data[" . $fieldIndex . "][isBase]", 0);?>
                            <?=CHtml::hidden("data[" . $fieldIndex . "][field]", $fieldID);?>
                        </li>
                    <?
                    $fieldIndex++;
                }
            ?>
        </ul>
    </div>
    <div class="col-sm-6">
        <form class="form-horizontal view_form" method="POST" action="<?=BASE_URL . "ajax/";?>">
            <?=CHtml::hidden("widget", $this->name);?>
            <?=CHtml::hidden("method", "saveViewSettings");?>
            <?=CHtml::hidden("entityID", $obEntity->entity_id);?>
            <?=CHtml::hidden("relation", $relation);?>
            <?=CHtml::hidden("type", $type);?>
            <?
                if($entitySectionID){
                    echo CHtml::hidden("entitySectionID", $entitySectionID);
                }
            ?>
            <h5>Список выбранных полей:</h5>
            <ul class="chosen_fields_list fields_list connected_sortable">
                <?
                    foreach($arEntityDisplay AS $arField){
                        if($arField["isBase"]){
                            $fieldName      = $arField["field"];
                            $arFieldInfo    = $arBaseFields[$fieldName];
                            
                            if($arFieldInfo){
                                ?>
                                    <li data-is-base="1" data-field="<?=$fieldName;?>">
                                        <div class="field_drag_handle"><i class="icon-move"></i></div>
                                        (Осн. поле) <?=$arFieldInfo["title"];?>
                                        <?=CHtml::hidden("data[" . $fieldIndex . "][isBase]", 1);?>
                                        <?=CHtml::hidden("data[" . $fieldIndex . "][field]", $fieldName);?>
                                    </li>
                                <?
                                $fieldIndex++;
                            }
                        }else{
                            $fieldID = $arField["field"];
                            $obField = $arEntityFields[$fieldID];
                            
                            if($obField){
                                ?>
                                    <li data-is-base="0" data-field="<?=$fieldID;?>">
                                        <div class="field_drag_handle"><i class="icon-move"></i></div>
                                        (Доп. поле) <?=$obField->title;?>
                                        <?=CHtml::hidden("data[" . $fieldIndex . "][isBase]", 0);?>
                                        <?=CHtml::hidden("data[" . $fieldIndex . "][field]", $fieldID);?>
                                    </li>
                                <?
                                $fieldIndex++;
                            }
                        }
                    }
                ?>
            </ul>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-actions text-right">
                        <button type="submit" class="btn btn-primary"><i class="icon-checkmark"></i> Применить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>