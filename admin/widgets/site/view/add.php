<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Новый сайт<small>Добавление</small></h3>
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
<form class="form-horizontal site_form" method="POST" action="<?=$addUrl;?>">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">Новый сайт</h6>
        </div>
        <div class="panel-body">
            <div class="form-group<?=($arErrors["site_id"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">ID:<span class="mandatory">*</span></label>
                <div class="col-sm-2">
                    <?=CHtml::text("site[site_id]", $arFormData["site_id"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите идентификатор сайта.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["active"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Активность:</label>
                <div class="col-sm-6">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::boolean("site[active]", array(1, 0), $arFormData["active"], array(
                            "id" => "is_active"
                        ));?>
                        <label for="is_active"></label>
                    </div>
                </div>
            </div>
            <div class="form-group<?=($arErrors["title"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Название:<span class="mandatory">*</span></label>
                <div class="col-sm-6">
                    <?=CHtml::text("site[title]", $arFormData["title"], array(
                        "class" => "form-control"
                    ));?>
                    <span class="help-block">Введите название сайта.</span>
                </div>
            </div>
            <div class="form-group<?=($arErrors["domains"] ? " has-error" : "")?>">
                <label class="col-sm-2 control-label">Список доменов:</label>
                <div class="col-sm-6">
                    <?
                        if(is_array($arFormData["domains"])){
                            $domains = implode(PHP_EOL, $arFormData["domains"]);
                        }else if($arFormData["domains"] == "*"){
                            $domains = "";
                        }else{
                            $domains = $arFormData["domains"];
                        }
                    ?>
                    <?=CHtml::textarea("site[domains]", $domains, array(
                        "class" => "form-control",
                        "style" => "resize: vertical;height: 180px;"
                    ));?>
                    <span class="help-block">Укажите список доменов через запятую или с новой строки. Если домен не важен, оставьте поле пустым</span>
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
    $("form.site_form").find("select").select({
        inputEnable: false,
        markMatch: true
    });
    
    $("form.site_form").on("submit", function(){
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
                                $formItems.find('[name^="site[' + field + ']"]')
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