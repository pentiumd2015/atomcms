<?
if(CHttpRequest::isAjax()){
    ?>
        <script type="text/javascript" src="<?=$this->path . "js/list.js";?>"></script>
        <link href="<?=$this->path . "css/list.css";?>" rel="stylesheet" type="text/css" />
    <?
}else{
    CPage::addJS($this->path . "js/list.js");
    CPage::addCSS($this->path . "css/list.css");
}
?>
<div class="row list_settings_container">
    <div class="col-sm-6">
        <h5>Список доступных колонок:</h5>
        <ul class="available_fields_list fields_list connected_sortable">
            <?
                foreach($obDisplay->getAllVisibleFields() AS $fieldName => $obField){
                    if(isset($arEntityDisplay[$fieldName])){
                        continue;
                    }
                    ?>
                        <li>
                            <div class="field_drag_handle"><i class="icon-move"></i></div>
                            <?=$obField->title;?>
                            <?=CHtml::hidden("data[][field]", $fieldName);?>
                        </li>
                    <?
                }
            ?>
        </ul>
    </div>
    <div class="col-sm-6">
        <form class="list_settings_form" method="POST" action="<?=BASE_URL . "ajax/";?>">
            <?=CHtml::hidden("widget", $this->name);?>
            <?=CHtml::hidden("method", "setDisplaySettings");?>
            <?=CHtml::hidden("entity", $obEntity->getClass());?>
            <?=CHtml::hidden("type", "list");?>
            <h5>Список выбранных колонок:</h5>
            <ul class="chosen_fields_list fields_list connected_sortable">
                <?
                    foreach($arEntityDisplay AS $fieldName => $obField){
                        ?>
                            <li>
                                <div class="field_drag_handle"><i class="icon-move"></i></div>
                                <?=$obField->title;?>
                                <?=CHtml::hidden("data[][field]", $fieldName);?>
                            </li>
                        <?
                    }
                ?>
            </ul>
        </form>
    </div>
</div>