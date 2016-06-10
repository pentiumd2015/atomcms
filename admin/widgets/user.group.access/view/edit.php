<?
use \Helpers\CHtml;
use \Models\UserGroupAccess;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$arFormData["title"];?><small>Редактирование правила</small></h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка изменения правила</h5>
                <p>При изменении правила возникли ошибки.</p>
            </div>
        <?
    }
?>
<form class="form-horizontal user_group_access_form" method="POST" action="<?=$addUrl;?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title"><?=$arFormData["title"];?></h6>
        </div>
        <div class="panel-body">
            <?
                if($arFormData["alias"] == UserGroupAccess::ADMIN_ACCESS){
                    ?>
                        <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Название правила:<span class="mandatory">*</span></label>
                            <div class="col-sm-6">
                                <?=CHtml::text("user_group_access[title]", $arFormData["title"], array(
                                    "class"     => "form-control",
                                    "disabled"  => "disabled"
                                ));?>
                                <span class="help-block">Введите название правила. Например: Редактирование контента</span>
                            </div>
                        </div>
                        <div class="form-group<?=($arErrors["alias"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Алиас:<span class="mandatory">*</span></label>
                            <div class="col-sm-6">
                                <?=CHtml::text("user_group_access[alias]", $arFormData["alias"], array(
                                    "class"     => "form-control",
                                    "disabled"  => "disabled"
                                ));?>
                                <span class="help-block">Алиас может содержать латинские буквы и -_ без пробелов. Например: edit_content</span>
                            </div>
                        </div>
                        <div class="form-group<?=($arErrors["description"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Описание:</label>
                            <div class="col-sm-6">
                                <?=CHtml::textarea("user_group_access[description]", $arFormData["description"], array(
                                    "class"     => "form-control",
                                    "style"     => "resize: vertical;height: 180px;",
                                    "disabled"  => "disabled"
                                ));?>
                            </div>
                        </div>
                    <?
                }else{
                    ?>
                        <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Название правила:<span class="mandatory">*</span></label>
                            <div class="col-sm-6">
                                <?=CHtml::text("user_group_access[title]", $arFormData["title"], array(
                                    "class" => "form-control"
                                ));?>
                                <span class="help-block">Введите название правила. Например: Редактирование контента</span>
                            </div>
                        </div>
                        <div class="form-group<?=($arErrors["alias"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Алиас:<span class="mandatory">*</span></label>
                            <div class="col-sm-6">
                                <?=CHtml::text("user_group_access[alias]", $arFormData["alias"], array(
                                    "class" => "form-control"
                                ));?>
                                <span class="help-block">Алиас может содержать латинские буквы и -_ без пробелов. Например: edit_content</span>
                            </div>
                        </div>
                        <div class="form-group<?=($arErrors["description"] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label">Описание:</label>
                            <div class="col-sm-6">
                                <?=CHtml::textarea("user_group_access[description]", $arFormData["description"], array(
                                    "class" => "form-control",
                                    "style" => "resize: vertical;height: 180px;"
                                ));?>
                            </div>
                        </div>
                        <div class="form-actions text-right">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    <?
                }
            ?>
        </div>
    </div>
</form>
<script type="text/javascript">
$(function(){
    $("form.user_group_access_form").find("select").select({
        inputEnable: false,
        markMatch: true
    });
    
    $("form.user_group_access_form").on("submit", function(){
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
                                $formItems.find('[name^="user_group_access[' + field + ']"]')
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