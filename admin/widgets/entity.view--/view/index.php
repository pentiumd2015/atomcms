<?
use \Helpers\CHtml;
use \Entities\EntityBaseField;
use \Entities\EntityField;

?>
<div class="page-header">
    <div class="page-title">
        <h3>Настройка вида<small>подробный просмотр элементов</small></h3>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="radio radio-primary">
            <?$inheritTypeItem = 1;?>
            <?=CHtml::radio("entity_view[inherit][type]", ($inheritType == $inheritTypeItem), array(
                "value" => $inheritTypeItem,
                "id"    => "inherit_type_" . $inheritTypeItem
            ));?>
            <label for="inherit_type_<?=$inheritTypeItem;?>">Использовать вид по умолчанию</label>
        </div>
        <?
            if($obEntity->use_sections){
                ?>
                    <div class="radio radio-primary">
                        <?$inheritTypeItem = 2;?>
                        <?=CHtml::radio("entity_view[inherit][type]", ($inheritType == $inheritTypeItem), array(
                            "value" => $inheritTypeItem,
                            "id"    => "inherit_type_" . $inheritTypeItem
                        ));?>
                        <label for="inherit_type_<?=$inheritTypeItem;?>">Наследовать вид от раздела</label>
                    </div>
                    <div id="inherit_section_container">
                        <?=CHtml::select("entity_view[inherit][section]", array(), "");?>
                    </div>
                <?
            }
        ?>
        <div class="radio radio-primary">
            <?$inheritTypeItem = 3;?>
            <?=CHtml::radio("entity_view[inherit][type]", ($inheritType == $inheritTypeItem), array(
                "value" => $inheritTypeItem,
                "id"    => "inherit_type_" . $inheritTypeItem
            ));?>
            <label for="inherit_type_<?=$inheritTypeItem;?>">Использовать свою структуру</label>
        </div>
    </div>
</div>
<div class="tabbable tabs_container">
    <ul class="nav nav-tabs">
         <?
            foreach($arEntityView AS $tabIndex => $arViewTab){
                $activeTab = ($tabIndex == 0);
                ?>
                    <li<?=($activeTab ? ' class="active"' : "");?>>
                        <a href="#view_tab_<?=$tabIndex;?>" data-toggle="tab" data-index="<?=$tabIndex;?>" data-title="<?=htmlspecialchars($arViewTab["title"]);?>">
                            <span class="tab_title"><?=$arViewTab["title"];?></span>
                            <div class="nav-tab-panel">
                                <span class="tab_edit">
                                    <i class="icon-pencil"></i>
                                </span>
                                <?
                                    if($tabIndex > 0){
                                        ?>
                                            <span class="tab_remove">
                                                <i class="icon-cancel-circle2"></i>
                                            </span>
                                        <?
                                    }
                                ?>
                            </div>
                        </a>
                    </li>
                <?
            }
        ?>
        <li>
            <a href="#" class="tab_add">
                <i class="icon-plus"></i>
                <div class="clearfix"></div>
            </a>
        </li>
    </ul>
    <form class="form-horizontal view_form" method="POST" action="<?=$editItemURL;?>">
        <div class="tab-content with-padding">
            <?
                $obEntityBaseField  = new EntityBaseField;
                $arFieldsInfo       = $obEntityBaseField->getInfo();
                
                foreach($arEntityView AS $tabIndex => $arViewTab){
                    $activeTab = ($tabIndex == 0);
                    ?>
                        <div class="tab-pane fade in<?=($activeTab ? " active" : "");?>" id="view_tab_<?=$tabIndex;?>" data-index="<?=$tabIndex;?>" data-title="<?=htmlspecialchars($arViewTab["title"]);?>">
                            <div class="row">
                                <div class="col-sm-6">
                                    <a href="#" class="btn btn-info field_add">Добавить поля</a>
                                    <a href="#" class="btn btn-primary group_add">Добавить группу полей</a>
                                    <table class="view_table">
                                        <tbody>
                                        <?
                                            if($arViewTab["items"]){
                                                foreach($arViewTab["items"] AS $itemIndex => $arTabItem){
                                                    if($arTabItem["type"] == "field"){ // field
                                                        if($arTabItem["isBase"]){
                                                            $fieldName      = $arTabItem["field"];
                                                            $arFieldInfo    = $arFieldsInfo[$fieldName];
                                                            if($arFieldInfo){
                                                                ?>
                                                                    <tr data-type="<?=$arTabItem["type"];?>" data-is-base="<?=(int)$arTabItem["isBase"];?>" data-field="<?=$fieldName;?>" data-index="<?=$itemIndex;?>">
                                                                        <td style="width: 40px;">
                                                                            <div class="drag_handle_1"><i class="icon-move"></i></div>
                                                                        </td>
                                                                        <td><?=$arFieldInfo["title"];?></td>
                                                                        <td class="view_table_nav">
                                                                            <div class="table-controls">
                                                                                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?
                                                            }
                                                        }else{
                                                            $fieldID = $arTabItem["field"];
                                                            $obField = $arEntityFields[$fieldID];
                                                            
                                                            if($obField){
                                                                ?>
                                                                    <tr data-type="<?=$arTabItem["type"];?>" data-is-base="<?=(int)$arTabItem["isBase"];?>" data-field="<?=$fieldID;?>" data-index="<?=$itemIndex;?>">
                                                                        <td style="width: 40px;">
                                                                            <div class="drag_handle_1"><i class="icon-move"></i></div>
                                                                        </td>
                                                                        <td><?=$obField->title;?></td>
                                                                        <td class="view_table_nav">
                                                                            <div class="table-controls">
                                                                                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?
                                                            }
                                                        }
                                                    }else if($arTabItem["type"] == "group"){ //group field
                                                        ?>
                                                            <tr data-type="<?=$arTabItem["type"];?>" data-title="<?=htmlspecialchars($arTabItem["title"]);?>" data-index="<?=$itemIndex;?>">
                                                                <td style="width: 40px;">
                                                                    <div class="drag_handle_1"><i class="icon-move"></i></div>
                                                                </td>
                                                                <td>
                                                                    <p class="group_title"><?=$arTabItem["title"]?></p>
                                                                    <table class="view_table_group">
                                                                        <tbody>
                                                                            <?
                                                                                if($arTabItem["fields"]){
                                                                                    foreach($arTabItem["fields"] AS $fieldIndex => $arTabField){ //fields
                                                                                        if($arTabField["isBase"]){
                                                                                            $fieldName      = $arTabField["field"];
                                                                                            $arFieldInfo    = $arFieldsInfo[$fieldName];
                                                                                            if($arFieldInfo){
                                                                                                ?>
                                                                                                    <tr data-type="field" data-is-base="<?=(int)$arTabField["isBase"];?>" data-field="<?=$fieldName;?>" data-index="<?=$fieldIndex;?>">
                                                                                                        <td style="width: 40px;">
                                                                                                            <div class="drag_handle_2"><i class="icon-move"></i></div>
                                                                                                        </td>
                                                                                                        <td><?=$arFieldInfo["title"];?></td>
                                                                                                        <td class="view_table_nav">
                                                                                                            <div class="table-controls">
                                                                                                                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
                                                                                                            </div>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                <?
                                                                                            }
                                                                                        }else{
                                                                                            $fieldID = $arTabField["field"];
                                                                                            $obField = $arEntityFields[$fieldID];
                                                        
                                                                                            if($obField){
                                                                                                ?>
                                                                                                    <tr data-type="field" data-is-base="<?=(int)$arTabField["isBase"];?>" data-field="<?=$fieldID;?>" data-index="<?=$fieldIndex;?>">
                                                                                                        <td style="width: 40px;">
                                                                                                            <div class="drag_handle_2"><i class="icon-move"></i></div>
                                                                                                        </td>
                                                                                                        <td><?=$obField->title;?></td>
                                                                                                        <td class="view_table_nav">
                                                                                                            <div class="table-controls">
                                                                                                                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
                                                                                                            </div>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                <?
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                <td class="view_table_nav">
                                                                    <div class="table-controls">
                                                                        <a href="#" class="btn btn-primary btn-icon btn-xs group_edit"><i class="icon-pencil"></i></a>
                                                                        <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?
                                                    }
                                                }
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?
                }
            ?>
            <div class="form-actions text-right">
                <a href="<?=$editItemURL;?>" class="btn btn-danger"><i class="icon-close"></i> Отмена</a>
                <button type="submit" class="btn btn-primary"><i class="icon-checkmark"></i> Применить</button>
            </div>
        </div>
    </form>
</div>

<!--Add Tab-->
<div id="view_tab_add_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-pencil"></i> Название вкладки</h4>
            </div>
            <div class="modal-body with-padding">
                <?=CHtml::text("", "", array(
                    "class" => "form-control tab_add_input"
                ));?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info tab_add_apply">Добавить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="tab_template">
    <li>
        <a href="#view_tab_#index#" data-toggle="tab" data-index="#index#" data-title="#title#">
            <span class="tab_title">#title#</span>
            <div class="nav-tab-panel">
                <span class="tab_edit">
                    <i class="icon-pencil"></i>
                </span>
                <span class="tab_remove">
                    <i class="icon-cancel-circle2"></i>
                </span>
            </div>
        </a>
    </li>
</script>

<script type="text/template" id="tab_content_template">
    <div class="tab-pane fade in" id="view_tab_#index#" data-index="#index#">
        <div class="row">
            <div class="col-sm-6">
                <a href="#" class="btn btn-info field_add">Добавить поля</a>
                <a href="#" class="btn btn-primary group_add">Добавить группу полей</a>
                <table class="view_table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</script>
<!--Add Tab-->

<!--Edit Tab-->
<div id="view_tab_edit_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-pencil"></i> Название вкладки</h4>
            </div>
            <div class="modal-body with-padding">
                <?=CHtml::text("", "", array(
                    "class" => "form-control tab_edit_input"
                ));?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info tab_edit_apply">Применить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<!--Edit Tab-->

<!--Add Field-->
<div id="view_field_add_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-pencil"></i> Список доступных полей</h4>
            </div>
            <div class="modal-body with-padding">
                <?=CHtml::multiselect("", array(), array(), array(
                    "class" => "select_available_fields"
                ));?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info field_add_apply">Применить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="field_template">
    <tr data-type="field" data-is-base="#isBase#" data-field="#field#" data-index="#index#">
        <td style="width: 40px;">
            <div class="#dragHandle#"><i class="icon-move"></i></div>
        </td>
        <td>#title#</td>
        <td class="view_table_nav">
            <div class="table-controls">
                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
            </div>
        </td>
    </tr>
</script>
<!--Add Field-->

<!--Add Group-->
<div id="view_group_add_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-pencil"></i> Новая группа</h4>
            </div>
            <div class="modal-body with-padding">
                <div class="form-group">
                    <label>Название группы: </label>
                    <div>
                        <?=CHtml::text("", "", array(
                            "class" => "form-control group_add_value"
                        ));?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Список доступных полей: </label>
                    <div>
                        <?=CHtml::multiselect("", array(), array(), array(
                            "class" => "select_available_fields"
                        ));?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info group_add_apply">Применить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="group_template">
    <tr data-type="group" data-title="#title#" data-index="#index#">
        <td style="width: 40px;">
            <div class="drag_handle_1"><i class="icon-move"></i></div>
        </td>
        <td>
            <p class="group_title">#title#</p>
            <table class="view_table_group">
                <tbody>#fieldItems#</tbody>
            </table>
        </td>
        <td class="view_table_nav">
            <div class="table-controls">
                <a href="#" class="btn btn-primary btn-icon btn-xs group_edit"><i class="icon-pencil"></i></a>
                <a href="#" class="btn btn-danger btn-icon btn-xs entity_view_remove"><i class="icon-remove"></i></a> 
            </div>
        </td>
    </tr>
</script>
<!--Add Group-->

<!--Edit Group-->
<div id="view_group_edit_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-pencil"></i> Редактирование группы</h4>
            </div>
            <div class="modal-body with-padding">
                <div class="form-group">
                    <label>Название группы: </label>
                    <div>
                        <?=CHtml::text("", "", array(
                            "class" => "form-control group_edit_value"
                        ));?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Список доступных полей: </label>
                    <div>
                        <?=CHtml::multiselect("", array(), array(), array(
                            "class" => "select_available_fields"
                        ));?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info group_edit_apply">Применить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<!--Edit Group-->

<?
/*Получим весь список основных полей*/
$arBaseFields = array();

$obEntityBaseField  = new EntityBaseField;

foreach($obEntityBaseField->getInfo() AS $fieldName => $arFieldInfo){
    $arBaseFields[$fieldName] = array(
        "field"  => $fieldName,
        "title"  => $arFieldInfo["title"]
    );
}
/*Получим весь список основных полей*/
?>
<script type="text/javascript">
    var obViewSettings              = new ViewSettings;
    obViewSettings.arBaseFields     = <?=\Helpers\CHttpResponse::toJSON($arBaseFields);?>;
    obViewSettings.arExtraFields    = <?=\Helpers\CHttpResponse::toJSON($arEntityFields);?>;
    obViewSettings.params           = {
        ajaxURL     : "<?=BASE_URL;?>ajax/",
        obEntityItem: <?=\Helpers\CHttpResponse::toJSON($obEntityItem);?>,
        editItemURL : "<?=$editItemURL;?>"
    };
</script>

<?php 

?>

<?php 

?>