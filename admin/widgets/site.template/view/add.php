<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Новый шаблон<small>Добавление</small></h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка изменения</h5>
                <p>При изменении возникли ошибки.</p>
            </div>
        <?
    }
?>
<form class="form-horizontal template_form" method="POST" action="<?=$editUrl;?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">Новый шаблон</h6>
        </div>
        <div class="panel-body">
            <div class="form-group<?=($arErrors["template_id"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">ID:<span class="mandatory">*</span></label>
                <div class="col-sm-2">
                    <?=CHtml::text("template[template_id]", $arFormData["template_id"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите идентификатор шаблона. Идентификатор может содержать латинские символы, а также цифры и _-</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Название:<span class="mandatory">*</span></label>
                <div class="col-sm-6">
                    <?=CHtml::text("template[title]", $arFormData["title"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите название шаблона.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["path"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Путь до папки:<span class="mandatory">*</span></label>
                <div class="col-sm-6">
                    <?=CHtml::text("template[path]", $arFormData["path"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Укажите путь до папки шаблона. Путь может содержать латинские символы, а также цифры и _-.</span>
                    <span class="help-block">Текущий путь: <span class="label label-primary" id="template_path"><?=str_replace("{TEMPLATE_PATH}", $arFormData["path"], $templatePath);?></span></span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["description"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Описание:</label>
                <div class="col-sm-6">
                    <?=CHtml::textarea("template[description]", $arFormData["description"], array(
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
    $('input[name="template[path]"]').on("keyup change", function(){
        $("#template_path").html("<?=$templatePath;?>".replace("{TEMPLATE_PATH}", $(this).val()));
    });
    
    $("form.template_form").on("submit", function(){
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
                                $formItems.find('[name^="template[' + field + ']"]')
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