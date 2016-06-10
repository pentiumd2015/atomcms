<?
use \Helpers\CHtml;
?>
<div class="form-group<?=($arErrors["active"] ? " has-error" : "")?>">
    <label class="col-sm-2 control-label">Активность:</label>
    <div class="col-sm-6">
        <div class="checkbox checkbox-primary">
            <?=CHtml::boolean("route[active]", array(1, 0), $arFormData["active"], array(
                "id" => "is_active"
            ));?>
            <label for="is_active"></label>
        </div>
    </div>
</div>
<div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
    <label class="col-sm-2 control-label">Название:</label>
    <div class="col-sm-6">
        <?=CHtml::text("route[title]", $arFormData["title"], array(
            "class" => "form-control"
        ));?>
    </div>
</div>
<div class="form-group<?=($arErrors["path"] ? " has-error" : "")?>">
    <label class="col-sm-2 control-label">Путь страницы:<span class="mandatory">*</span></label>
    <div class="col-sm-6">
        <?=CHtml::text("route[path]", $arFormData["path"], array(
            "class" => "form-control"
        ));?>
    </div>
</div>