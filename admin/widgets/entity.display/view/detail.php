<?
if(CHttpRequest::isAjax()){
    ?>
        <script type="text/javascript" src="<?=$this->path . "js/detail.js";?>"></script>
        <link href="<?=$this->path . "css/detail.css";?>" rel="stylesheet" type="text/css" />
    <?
}else{
    CPage::addJS($this->path . "js/detail.js");
    CPage::addCSS($this->path . "css/detail.css");
}
?>
<script type="text/javascript">
    <?
    $arVisibleFieldNames = [];
    foreach($obDisplay->getAllVisibleFields() AS $fieldName => $obField){
        $arVisibleFieldNames[$fieldName] = $obField->title;
    }
    ?>
    $.detailSettings.setFields(<?=CJSON::encode($arVisibleFieldNames);?>);
</script>
<div class="tabbable" id="detail_settings_container">
    <ul class="nav nav-tabs">
         <?
            foreach($arEntityDisplay AS $tabIndex => $arTab){
                $activeTab = ($tabIndex == 0);
                ?>
                    <li<?=($activeTab ? ' class="active"' : "");?>>
                        <a href="#view_tab_<?=$tabIndex;?>" data-toggle="tab" data-index="<?=$tabIndex;?>">
                            <div class="tab_drag_handle">
                                <i class="icon-move"></i>
                            </div>
                            <span class="tab_title"><?=$arTab["title"];?></span>
                            <div class="nav-tab-panel">
                                <span class="tab_edit" onclick="$.detailSettings.showEditTabPopup(this)">
                                    <i class="icon-pencil"></i>
                                </span>
                                <?
                                    if($tabIndex > 0){
                                        ?>
                                            <span class="tab_remove" onclick="$.detailSettings.deleteTab(this)">
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
            <a href="javascript:($.detailSettings.showNewTabPopup());" class="tab_add">
                <i class="icon-plus"></i>
                <div class="clearfix"></div>
            </a>
        </li>
    </ul>
    <form class="form-horizontal display_form" method="POST" action="<?=BASE_URL . "ajax/";?>">
        <?=CHtml::hidden("widget", $this->name);?>
        <?=CHtml::hidden("method", "setDisplaySettings");?>
        <?=CHtml::hidden("entity", $obEntity->getClass());?>
        <?=CHtml::hidden("type", "detail");?>
        <div class="tab-content with-padding">
            <?
                $tabIndex   = 0;
                $itemIndex  = 0;
                
                foreach($arEntityDisplay AS $arTab){
                    $activeTab = ($tabIndex == 0);
                    ?>
                        <div class="tab-pane fade in<?=($activeTab ? " active" : "");?>" id="view_tab_<?=$tabIndex;?>" data-index="<?=$tabIndex;?>">
                            <div class="row">
                                <div class="col-sm-12">
                                    <?=CHtml::hidden("data[" . $tabIndex . "][title]", $arTab["title"], [
                                        "class" => "data_tab_title"
                                    ]);?>
                                    <a href="#" class="btn btn-info" onclick="$.detailSettings.showNewFieldPopup(this); return false;">Добавить поля</a>
                                </div>
                            </div>
                            <div class="row row-margin">
                                <div class="col-sm-12">    
                                    <ul class="display_list">
                                        <?
                                            if($arTab["fields"]){
                                                foreach($arTab["fields"] AS $fieldName => $obField){
                                                    ?>
                                                        <li data-index="<?=$itemIndex;?>">
                                                            <div class="row">
                                                                <div class="col-sm-1 drag_handle_1">
                                                                    <i class="icon-move"></i>
                                                                    <?=CHtml::hidden("data[" . $tabIndex . "][fields][" . $itemIndex . "][field]", $fieldName, [
                                                                        "class" => "data_item_field"
                                                                    ]);?>
                                                                </div>
                                                                <div class="col-sm-9 display_item_title"><?=$obField->title;?></div>
                                                                <div class="col-sm-2 text-right">
                                                                    <a href="#" class="btn btn-primary btn-icon btn-xs" onclick="$.detailSettings.deleteField(this)">
                                                                        <i class="icon icon-close"></i>
                                                                    </a> 
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?
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
        </div>
    </form>
</div>
<script type="text/template" id="detail_settings_tab_template">
    <li>
        <a href="#detail_settings_tab_#index#" data-toggle="tab" data-index="#index#">
            <div class="tab_drag_handle">
                <i class="icon-move"></i>
            </div>
            <span class="tab_title">#title#</span>
            <div class="nav-tab-panel">
                <span class="tab_edit" onclick="$.detailSettings.showEditTabPopup(this)">
                    <i class="icon-pencil"></i>
                </span>
                <span class="tab_remove" onclick="$.detailSettings.deleteTab(this)">
                    <i class="icon-cancel-circle2"></i>
                </span>
            </div>
        </a>
    </li>
</script>

<script type="text/template" id="detail_settings_tab_content_template">
    <div class="tab-pane fade in" id="detail_settings_tab_#index#" data-index="#index#">
        <div class="row">
            <div class="col-sm-12">
                <?=CHtml::hidden("data[#index#][title]", "#title#", [
                    "class" => "data_tab_title"
                ]);?>
                <a href="#" class="btn btn-info" onclick="$.detailSettings.showNewFieldPopup(this); return false;">Добавить поля</a>
            </div>
        </div>
        <div class="row row-margin">
            <div class="col-sm-12">
                <ul class="display_list"></ul>
            </div>
        </div>
    </div>
</script>
<!--Add Tab-->

<!--Add Field-->
<script type="text/template" id="detail_settings_field_template">
    <li data-index="#index#">
        <div class="row">    
            <div class="col-sm-1 #dragHandle#">
                <i class="icon-move"></i>
                <?=CHtml::hidden("#hiddenName#[field]", "#field#", [
                    "class" => "data_item_field"
                ]);?>
            </div>
            <div class="col-sm-9 display_item_title">#title#</div>
            <div class="col-sm-2 text-right">
                <a href="#" class="btn btn-primary btn-icon btn-xs" onclick="$.detailSettings.deleteField(this)">
                    <i class="icon icon-close"></i>
                </a>
            </div>
        </div>
    </li>
</script>
<!--Add Field-->