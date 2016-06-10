<?
use \Helpers\CHtml;
use \Entities\EntityItem;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?>, Новое поле</h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка добавления поля</h5>
                <p>При добавлении поля возникли ошибки.</p>
            </div>
        <?
    }
?>
<form class="form-horizontal entity_field_form" method="POST" action="<?=$addURL;?>">
    <?=CHtml::hidden("entity_field[entity_id]", $obEntity->entity_id);?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">Новое поле</h6>
        </div>
        <div class="panel-body">
            <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Название поля:<span class="mandatory">*</span> </label>
                <div class="col-sm-6">
                    <?=CHtml::text("entity_field[title]", $arFormData["title"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите название поля. Например: Стоимость</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["description"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Описание: </label>
                <div class="col-sm-6">
                    <?=CHtml::textarea("entity_field[description]", $arFormData["description"], array(
                        "class" => "form-control",
                        "rows"  => 5
                    ));?>
                </div>
            </div>
            <div class="form-group<?=($arErrors["priority"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Сортировка: </label>
                <div class="col-sm-2">
                    <?=CHtml::text("entity_field[priority]", $arFormData["priority"], array(
                        "class"     => "form-control"
                    ));?>
                    <span class="help-block">Сортировка влияет на отображение сущности в таблице структуры системы. <br/>Чем меньше число, тем выше будет находиться сущность</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["is_multi"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label" for="is_multi">Множественное: </label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::boolean("entity_field[is_multi]", array(1, 0), $arFormData["is_multi"], array(
                            "id"        => "is_multi"
                        ));?>
                        <label for="is_multi"></label>
                    </div>
                    <span class="help-block">Для множественных полей есть возможность добавлять несколько значений вместо одного.</span>
                </div>
            </div>
            <?
                if($obEntity->use_sections){
                    ?>
                        <div class="form-group<?=($arErrors["relation"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Связь: </label>
                            <div class="col-sm-6">
                                <?
                                    foreach(EntityItem::$arTypes AS $value => $arRelationItem){
                                        ?>
                                            <div class="radio radio-primary">
                                                <?=CHtml::radio("entity_field[relation]", ($value == $arFormData["relation"]), array(
                                                    "id"        => "relation_" . $value,
                                                    "value"     => $value
                                                ));?>
                                                <label for="relation_<?=$value;?>"><?=$arRelationItem["title"];?></label>
                                            </div>
                                        <?
                                    }
                                ?>
                                <span class="help-block">Выберите раздел, если поле будет заполняться в разделах, <br/>либо элемент для заполнения поля в элементах.</span>
                            </div>
                        </div>
                    <?
                }
            ?>
            <div class="form-group<?=($arErrors["is_required"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label" for="is_required">Обязательное: </label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::boolean("entity_field[is_required]", array(1, 0), $arFormData["is_required"], array(
                            "id"        => "is_required"
                        ));?>
                        <label for="is_required"></label>
                    </div>
                    <span class="help-block">Выберите эту опцию, если поле должно быть обязательным для заполнения.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["is_unique"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label" for="is_unique">Уникальное: </label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::boolean("entity_field[is_unique]", array(1, 0), $arFormData["is_unique"], array(
                            "id"        => "is_unique"
                        ));?>
                        <label for="is_unique"></label>
                    </div>
                    <span class="help-block">Если поле Уникальное, то при добавлении/измении элементов будет выполнена проверка на уже существующую запись с таким же значением, как у этого поля.<br />В случае дубликата, система выдаст предупреждение.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["is_entity_header"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label" for="is_entity_header">Используется как заголовок: </label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::checkbox("entity_field[is_entity_header]", $arFormData["is_entity_header"], array(
                            "id"        => "is_entity_header"
                        ));?>
                        <label for="is_entity_header"></label>
                    </div>
                    <span class="help-block">Если отмечена данная опция, то значение поля будет заголовком на странице элемента.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["type"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Тип:<span class="mandatory">*</span> </label>
                <div class="col-sm-6">
                    <?=CHtml::select("entity_field[type]", $arFieldTypeOptionList, $arFormData["type"], array(
                        "class" => "form-control",
                        "options" => array(
                            "" => array("disabled" => "disabled")
                        )
                    ));?>
                </div>
            </div>
            <div id="field_params_container"><?=$obFieldType ? $obFieldType->renderParams() : "";?></div>
            <div class="form-actions text-right">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
$(function(){
    function refreshFieldParams(){
        $container      = $("#field_params_container").empty();
        var formData    = $(".entity_field_form").serializeArray();
        
        var data = {};
        
        for(var i in formData){
            var item = formData[i];
            
            data[item.name] = item.value;
        }
        
        data = $.extend({
            widget  : "<?=$this->name;?>",
            method  : "getFieldParamsHtml"
        }, data);
        
        $.ajax({
            type: "POST",
            url: "<?=BASE_URL;?>ajax/",
            beforeSend: function(){
                $container.html('<div class="ajax_spinner"><i class="icon-spinner3 spin block-inner"></i></div>');
            },
            data: data,
            dataType: "json",
            success: function(r){
                if(r && r.result == 1){
                    if(!r.hasErrors){
                        $container.html(r.html);
                    }else{
                        console.log(r.errors);
                    }
                }
            }
        });
    }
    
    $('input[name="entity_field[priority]"]').inputNum({
        min: 0,
        max: Infinity
    });
    
    $(".entity_field_form select").select({
        inputEnable: false,
        markMatch: true
    });
    
    $(document).on("change", 'select[name="entity_field[type]"]', refreshFieldParams);
    $(document).on("change", 'input[name="entity_field[is_multi]"]', refreshFieldParams);
    
    $("form.entity_field_form").on("submit", function(){
        $form = $(this);
        
        $.note({
            title: "<i class=\"icon-spinner3 spin\"></i>&nbsp;&nbsp;Сохранение...", 
            theme: "info"
        });
        
        delay(function(){
            $.ajax({
                type    : $form.attr("method"),
                url     : $form.attr("action"),
                data    : $form.serialize(),
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        var $formItems = $form.find(".form-group").removeClass("has-error");
                        
                        if(!r.hasErrors){
                            location.href = r.redirectURL;
                        }else{
                            $.note({
                                header  : "Ошибка сохранения!", 
                                title: "Данные не были сохранены", 
                                theme: "error",
                                duration: 5000
                            });
                            
                            for(var field in r.errors){
                                $formItems.find('[name^="entity_field[' + field + ']"]')
                                          .closest(".form-group")
                                          .addClass("has-error");
                            }
                        }
                    }
                }
            });
        }, 200);
        
        return false;
    });
});
</script>