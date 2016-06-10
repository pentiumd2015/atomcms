<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Новая сущность</h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка добавления сущности</h5>
                <p>При добавлении сущности возникли ошибки.</p>
            </div>
        <?
    }
?>
<form class="form-horizontal entity_form" method="POST" action="<?=$addUrl;?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">
                Новая сущность
            </h6>
        </div>
        <div class="panel-body">
            <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Название сущности:<span class="mandatory">*</span> </label>
                <div class="col-sm-6">
                    <?=CHtml::text("entity[title]", $arFormData["title"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите название сущности. Например: Статьи</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["entity_group_id"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Группа: </label>
                <div class="col-sm-6">
                    <?=CHtml::select("entity[entity_group_id]", $arEntityGroupOptionList, $arFormData["entity_group_id"], array(
                        "class" => "form-control",
                    ));?>
                    <span class="help-block">Сущности можно объединять в группы. Например можно добавить группы: Общая, Справочники и т.д. <br/>Добавить группы сущностей можно <a href="<?=BASE_URL;?>settings/entity_groups/add/">здесь</a></span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["priority"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Сортировка:<span class="mandatory">*</span> </label>
                <div class="col-sm-6">
                    <?=CHtml::text("entity[priority]", $arFormData["priority"], array(
                        "class"     => "form-control"
                    ));?>
                    <span class="help-block">Сортировка влияет на отображение сущности в таблице структуры системы. <br/>Чем меньше число, тем выше будет находиться сущность</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["use_sections"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label" for="use_sections">Использовать разделы: </label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::boolean("entity[use_sections]", array(1, 0), $arFormData["use_sections"], array(
                            "class"     => "form-control",
                            "id"        => "use_sections"
                        ));?>
                        <label for="use_sections"></label>
                    </div>
                    
                    <span class="help-block">Разделы используются для объединения элементов в группы. <br/>Например, для сущности "Товары" разделами будут: "кофеварки", "телевизоры" и т.д.</span>
                </div>
            </div>
            <div class="form-actions text-right">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
$(function(){
    $('input[name="entity[priority]"]').inputNum({
        min: 0,
        max: Infinity
    });
    
    $(".entity_form select").select({
        inputEnable: false,
        markMatch: true
    });
    
    $("form.entity_form").on("submit", function(){
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
                        if(!r.hasErrors){
                            location.href = r.redirectURL;
                        }else{
                            $.note({
                                header  : "Ошибка сохранения!", 
                                title   : "Данные не были сохранены", 
                                theme   : "error",
                                duration: 5000
                            });
                            
                            var $formItems = $form.find(".form-group").removeClass("has-error");
                            
                            for(var field in r.errors){
                                $formItems.find('[name^="entity[' + field + ']"]')
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