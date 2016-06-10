<?
    if($arInfo["description"]){
        ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Описание: </label>
                <div class="col-sm-6"><?=$arInfo["description"];?></div>
            </div>
        <?
    }
?>
<div class="form-group<?=($arParams["errors"]["some"] ? " has-error" : "")?>">
    <label class="col-sm-2 control-label">Some: </label>
    <div class="col-sm-6">
        <?=\Helpers\CHtml::text("entity_field[params][some]", $arParams["some"], array(
            "class" => "form-control"
        ));?>
        <span class="help-block">Some help</span>
    </div>
</div>