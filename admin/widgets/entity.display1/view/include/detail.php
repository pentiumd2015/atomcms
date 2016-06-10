<?
use \Helpers\CHtml;
use \Entities\EntityItem;
use \Entities\EntityField;
use \Entities\EntityAdminDisplay;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?>, Настройка вида<small><?=EntityItem::$arTypes[$relation]["title"] . ". Подробный просмотр";?></small></h3>
    </div>
</div>
<div class="tabbable tabs_container">
    <ul class="nav nav-tabs" id="tabs_sortable">
         <?
            foreach($arEntityDisplay AS $tabIndex => $arViewTab){
                $activeTab = ($tabIndex == 0);
                ?>
                    <li<?=($activeTab ? ' class="active"' : "");?>>
                        <a href="#view_tab_<?=$tabIndex;?>" data-toggle="tab" data-index="<?=$tabIndex;?>">
                            <div class="tab_drag_handle">
                                <i class="icon-move"></i>
                            </div>
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
    <form class="form-horizontal display_form" method="POST" action="<?=BASE_URL . "ajax/";?>">
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
        <div class="tab-content with-padding">
            <?
                $tabIndex   = 0;
                $itemIndex  = 0;
                
                foreach($arEntityDisplay AS $arViewTab){
                    $activeTab = ($tabIndex == 0);
                    ?>
                        <div class="tab-pane fade in<?=($activeTab ? " active" : "");?>" id="view_tab_<?=$tabIndex;?>" data-index="<?=$tabIndex;?>">
                            <div class="row">
                                <div class="col-sm-6">
                                    <?=CHtml::hidden("data[" . $tabIndex . "][title]", htmlspecialchars($arViewTab["title"]), array(
                                        "class" => "data_tab_title"
                                    ));?>
                                    <a href="#" class="btn btn-info field_add">Добавить поля</a>
                                    <a href="#" class="btn btn-primary group_add">Добавить группу полей</a>
                                </div>
                            </div>
                            <div class="row row-margin">
                                <div class="col-sm-6">    
                                    <ul class="display_list">
                                        <?
                                            if($arViewTab["items"]){
                                                foreach($arViewTab["items"] AS $arTabItem){
                                                    if($arTabItem["type"] == "field"){ // field
                                                        if($arTabItem["isBase"]){
                                                            $fieldName      = $arTabItem["field"];
                                                            $arFieldInfo    = $arBaseFields[$fieldName];
                                                            if($arFieldInfo){
                                                                ?>
                                                                    <li data-index="<?=$itemIndex;?>">
                                                                        <div class="row">
                                                                            <div class="col-sm-1 drag_handle_1">
                                                                                <i class="icon-move"></i>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][type]", $arTabItem["type"], array(
                                                                                    "class" => "data_item_type"
                                                                                ));?>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][isBase]", (int)$arTabItem["isBase"], array(
                                                                                    "class" => "data_item_is_base"
                                                                                ));?>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][field]", $fieldName, array(
                                                                                    "class" => "data_item_field"
                                                                                ));?>
                                                                            </div>
                                                                            <div class="col-sm-9 display_item_title"><?=$arFieldInfo["title"];?></div>
                                                                            <div class="col-sm-2 text-right">
                                                                                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                                                                                    <i class="icon icon-close"></i>
                                                                                </a> 
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                <?
                                                            }
                                                        }else{
                                                            $fieldID = $arTabItem["field"];
                                                            $obField = $arEntityFields[$fieldID];
                                                            
                                                            if($obField){
                                                                ?>
                                                                    <li data-index="<?=$itemIndex;?>">
                                                                        <div class="row">
                                                                            <div class="col-sm-1 drag_handle_1">
                                                                                <i class="icon-move"></i>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][type]", $arTabItem["type"], array(
                                                                                    "class" => "data_item_type"
                                                                                ));?>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][isBase]", (int)$arTabItem["isBase"], array(
                                                                                    "class" => "data_item_is_base"
                                                                                ));?>
                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][field]", $fieldID, array(
                                                                                    "class" => "data_item_field"
                                                                                ));?>
                                                                            </div>
                                                                            <div class="col-sm-9 display_item_title"><?=$obField->title;?></div>
                                                                            <div class="col-sm-2 text-right">
                                                                                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                                                                                    <i class="icon icon-close"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                <?
                                                            }
                                                        }
                                                    }else if($arTabItem["type"] == "group"){ //group field
                                                        ?>
                                                            <li data-index="<?=$itemIndex;?>">
                                                                <div class="row">
                                                                    <div class="col-sm-1 drag_handle_1">
                                                                        <i class="icon-move"></i>
                                                                        <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][type]", $arTabItem["type"], array(
                                                                            "class" => "data_item_type"
                                                                        ));?>
                                                                        <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][title]", htmlspecialchars($arTabItem["title"]), array(
                                                                            "class" => "data_item_title"
                                                                        ));?>
                                                                    </div>
                                                                    <div class="col-sm-9 display_item_title">
                                                                        <p class="group_title"><?=$arTabItem["title"]?></p>
                                                                        <ul class="display_list_group">
                                                                            <?
                                                                                if($arTabItem["items"]){
                                                                                    $fieldIndex = 0;
                                                                                    foreach($arTabItem["items"] AS $arTabField){ //fields
                                                                                        if($arTabField["isBase"]){
                                                                                            $fieldName      = $arTabField["field"];
                                                                                            $arFieldInfo    = $arBaseFields[$fieldName];
                                                                                            if($arFieldInfo){
                                                                                                ?>
                                                                                                    <li data-index="<?=$fieldIndex;?>">
                                                                                                        <div class="row">
                                                                                                            <div class="col-sm-1 drag_handle_2">
                                                                                                                <i class="icon-move"></i>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][type]", "field", array(
                                                                                                                    "class" => "data_item_type"
                                                                                                                ));?>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][isBase]", (int)$arTabField["isBase"], array(
                                                                                                                    "class" => "data_item_is_base"
                                                                                                                ));?>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][field]", $fieldName, array(
                                                                                                                    "class" => "data_item_field"
                                                                                                                ));?>
                                                                                                            </div>
                                                                                                            <div class="col-sm-9 display_item_title"><?=$arFieldInfo["title"];?></div>
                                                                                                            <div class="col-sm-2 text-right">
                                                                                                                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                                                                                                                    <i class="icon icon-close"></i>
                                                                                                                </a>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </li>
                                                                                                <?
                                                                                            }
                                                                                        }else{
                                                                                            $fieldID = $arTabField["field"];
                                                                                            $obField = $arEntityFields[$fieldID];
                                                        
                                                                                            if($obField){
                                                                                                ?>
                                                                                                    <li data-index="<?=$fieldIndex;?>">
                                                                                                        <div class="row">
                                                                                                            <div class="col-sm-1 drag_handle_2">
                                                                                                                <i class="icon-move"></i>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][type]", "field", array(
                                                                                                                    "class" => "data_item_type"
                                                                                                                ));?>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][isBase]", (int)$arTabField["isBase"], array(
                                                                                                                    "class" => "data_item_is_base"
                                                                                                                ));?>
                                                                                                                <?=CHtml::hidden("data[" . $tabIndex . "][items][" . $itemIndex . "][items][" . $fieldIndex . "][field]", $fieldID, array(
                                                                                                                    "class" => "data_item_field"
                                                                                                                ));?>
                                                                                                            </div>
                                                                                                            <div class="col-sm-9 display_item_title"><?=$obField->title;?></div>
                                                                                                            <div class="col-sm-2 text-right">
                                                                                                                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                                                                                                                    <i class="icon icon-close"></i>
                                                                                                                </a>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </li>
                                                                                                <?
                                                                                            }
                                                                                        }
                                                                                        
                                                                                        $fieldIndex++;
                                                                                    }
                                                                                }
                                                                            ?>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="col-sm-2 text-right">
                                                                        <a href="#" class="btn btn-info btn-icon btn-xs group_edit"><i class="icon-pencil"></i></a>
                                                                        <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                                                                            <i class="icon icon-close"></i>
                                                                        </a> 
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?
                                                    }
                                                    $itemIndex++;
                                                }
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?
                    $tabIndex++;
                }
            ?>
            <div class="form-actions text-right">
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
        <a href="#view_tab_#index#" data-toggle="tab" data-index="#index#">
            <div class="tab_drag_handle">
                <i class="icon-move"></i>
            </div>
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
                <?=CHtml::hidden("data[#index#][title]", "#title#", array(
                    "class" => "data_tab_title"
                ));?>
                <a href="#" class="btn btn-info field_add">Добавить поля</a>
                <a href="#" class="btn btn-primary group_add">Добавить группу полей</a>
            </div>
        </div>
        <div class="row row-margin">
            <div class="col-sm-6">
                <ul class="display_list"></ul>
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
    <li data-index="#index#">
        <div class="row">    
            <div class="col-sm-1 #dragHandle#">
                <i class="icon-move"></i>
                <?=CHtml::hidden("#hiddenName#[type]", "field", array(
                    "class" => "data_item_type"
                ));?>
                <?=CHtml::hidden("#hiddenName#[isBase]", "#isBase#", array(
                    "class" => "data_item_is_base"
                ));?>
                <?=CHtml::hidden("#hiddenName#[field]", "#field#", array(
                    "class" => "data_item_field"
                ));?>
            </div>
            <div class="col-sm-9 display_item_title">#title#</div>
            <div class="col-sm-2 text-right">
                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                    <i class="icon icon-close"></i>
                </a>
            </div>
        </div>
    </li>
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
    <li data-index="#index#">
        <div class="row">    
            <div class="col-sm-1 drag_handle_1">
                <i class="icon-move"></i>
                <?=CHtml::hidden("data[#tabIndex#][items][#index#][type]", "group", array(
                    "class" => "data_item_type"
                ));?>
                <?=CHtml::hidden("data[#tabIndex#][items][#index#][title]", "#title#", array(
                    "class" => "data_item_title"
                ));?>
            </div>
            <div class="col-sm-9 display_item_title">
                <p class="group_title">#title#</p>
                <ul class="display_list_group">#fieldItems#</ul>
            </div>
            <div class="col-sm-2 text-right">
                <a href="#" class="btn btn-info btn-icon btn-xs group_edit"><i class="icon-pencil"></i></a>
                <a href="#" class="btn btn-primary btn-icon btn-xs entity_view_remove">
                    <i class="icon icon-close"></i>
                </a>
            </div>
        </div>
    </li>
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
<script type="text/javascript">
    var obViewSettings              = new ViewSettings;
    obViewSettings.arBaseFields     = <?=\Helpers\CJSON::encode($arBaseFields);?>;
    obViewSettings.arExtraFields    = <?=\Helpers\CJSON::encode($arEntityFields);?>;
</script>