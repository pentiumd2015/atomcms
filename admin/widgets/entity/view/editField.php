<form class="form-horizontal entity_field_form" method="POST">
    <?=CHtml::hidden("widget", $this->name);?>
    <?=CHtml::hidden("method", "saveFieldSettings");?>
    <?=CHtml::hidden("entity", $entityClass);?>
    <?
        if($fieldID){
            echo CHtml::hidden("entity_field[" . $fieldPk . "]", $fieldID);
        }
    ?>
    <div class="form-group">
        <label class="col-sm-2 control-label">Название поля:<span class="mandatory">*</span> </label>
        <div class="col-sm-10 control-content">
            <?=CHtml::text("entity_field[title]", $arData["title"], array(
                "class" => "form-control"
            ));?>
            <span class="help-block">Введите название поля. Например: Стоимость</span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Описание: </label>
        <div class="col-sm-10 control-content">
            <?=CHtml::textarea("entity_field[description]", $arData["description"], array(
                "class" => "form-control",
                "rows"  => 5
            ));?>
        </div>
    </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Сортировка: </label>
            <div class="col-sm-10 control-content">
                <?=CHtml::text("entity_field[priority]", $arData["priority"], array(
                    "class" => "form-control"
                ));?>
                <span class="help-block">Сортировка влияет на отображение сущности в таблице структуры системы. <br/>Чем меньше число, тем выше будет находиться сущность</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="multi">Множественное: </label>
            <div class="col-sm-10 control-content">
                <div class="checkbox checkbox-primary">
                    <?=CHtml::boolean("entity_field[multi]", array(1, 0), $arData["multi"], array(
                        "id"        => "multi",
                    ));?>
                    <label for="multi"></label>
                </div>
                <span class="help-block">Для множественных полей есть возможность добавлять несколько значений вместо одного.</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="required">Обязательное: </label>
            <div class="col-sm-10 control-content">
                <div class="checkbox checkbox-primary">
                    <?=CHtml::boolean("entity_field[required]", array(1, 0), $arData["required"], array(
                        "id" => "required"
                    ));?>
                    <label for="required"></label>
                </div>
                <span class="help-block">Выберите эту опцию, если поле должно быть обязательным для заполнения.</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="is_unique">Уникальное: </label>
            <div class="col-sm-10 control-content">
                <div class="checkbox checkbox-primary">
                    <?=CHtml::boolean("entity_field[is_unique]", array(1, 0), $arData["is_unique"], array(
                        "id"        => "is_unique"
                    ));?>
                    <label for="is_unique"></label>
                </div>
                <span class="help-block">Если поле Уникальное, то при добавлении/измении элементов будет выполнена проверка на уже существующую запись с таким же значением, как у этого поля.<br />В случае дубликата, система выдаст предупреждение.</span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Тип:<span class="mandatory">*</span> </label>
            <div class="col-sm-10 control-content">
                <?=CHtml::select("entity_field[type]", $arFieldTypeList, $arData["type"], array(
                    "class"     => "form-control",
                    "onchange"  => "$.entityFieldParams().refreshParams()",
                    "options"   => array(
                        "" => array("disabled" => "disabled")
                    )
                ));?>
            </div>
        </div>
        <div id="field_params_container"><?=$obFieldType ? $obFieldType->getRenderer()->renderParams($arData) : "";?></div>
</form>
<script type="text/javascript">
$(function(){
    var AdminFieldParams = function(){
        this.refreshParams = function(){
            var $container  = $("#field_params_container").empty();
            var formData    = $("form.entity_field_form").serializeArray();
            
            var data = {};
            
            for(var i in formData){
                var item = formData[i];
                
                if(item.name == "method"){
                    item.value = "getFieldParamsHtml"
                }
                
                data[item.name] = item.value;
            }
            
            $.ajax({
                type: "POST",
                url: "/admin/ajax/",
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
            });/**/
        }
        
        this.saveSettings = function(){
            var $form = $("form.entity_field_form");
            
            $.note({
                title: "<i class=\"icon-spinner3 spin\"></i>&nbsp;&nbsp;Сохранение...", 
                theme: "info"
            });
            
            delay(function(){
                $.ajax({
                    type    : $form.attr("method"),
                    url     : "/admin/ajax/",
                    data    : $form.serialize(),
                    dataType: "json",
                    success : function(r){
                        if(r && r.result == 1){
                            var $formItems = $form.find(".form-group");
                            
                            $formItems.removeClass("has-error")
                                      .find(".control-content")
                                      .find("label.error")
                                      .remove();
                            
                            if(!r.hasErrors){
                                location.reload();
                            }else{
                                $.note({
                                    header  : "Ошибка сохранения!", 
                                    title   : "Данные не были сохранены", 
                                    theme   : "error",
                                    duration: 5000
                                });
                                
                                for(var field in r.errors){
                                    var $formItem           = $formItems.find('[name^="entity_field[' + field + ']"]');
                                    var $formItemWrapper    = $formItem.closest(".form-group");
        
                                    $formItemWrapper.addClass("has-error")
                                                    .find(".control-content")
                                                    .append("<label class=\"error\">" + r.errors[field].message + "</label>");
                                }
                            }
                        }
                    }
                });
            }, 200);
        }
        
        $(document).on("change", 'input[name="entity_field[multi]"]', this.refreshParams);
        
        $('input[name="entity_field[priority]"]').inputNum({
            min: 0,
            max: Infinity
        });
    }

    if(typeof $.entityFieldParams == "undefined"){
        $.entityFieldParams = function(obj){
            if(typeof obj == "undefined"){
                return $(document).data("entityFieldParams");
            }else{
                $(document).data("entityFieldParams", obj)
                return obj;
            }
        }
    }
    
    $.entityFieldParams(new AdminFieldParams());
});
</script>