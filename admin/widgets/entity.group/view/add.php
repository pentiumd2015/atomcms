<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Новая группа</h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка добавления группы</h5>
                <p>При добавлении группы возникли ошибки.</p>
            </div>
        <?
    }
?>
<form class="form-horizontal entity_group_form" method="POST" action="<?=$addURL;?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">
                Новая группа
            </h6>
        </div>
        <div class="panel-body">
            <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Название группы:<span class="mandatory">*</span></label>
                <div class="col-sm-6">
                    <?=CHtml::text("entity_group[title]", $arFormData["title"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите название сущности. Например: Статьи</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["description"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Описание:</label>
                <div class="col-sm-6">
                    <?=CHtml::textarea("entity_group[description]", $arFormData["description"], array(
                        "class" => "form-control",
                        "style" => "resize: vertical;height: 180px;"
                    ));?>
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
    $("form.entity_group_form").on("submit", function(){
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
                                $formItems.find('[name^="entity_group[' + field + ']"]')
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