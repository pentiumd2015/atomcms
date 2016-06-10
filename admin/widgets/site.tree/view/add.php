<div class="page-header">
    <div class="page-title">
        <h3>Новая страница<small>Добавление страницы</small></h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="callout callout-danger fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h5>Ошибка добавления</h5>
                <p>При добавлении возникли ошибки.</p>
            </div>
        <?
    }
?>
<div class="tabbable">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#route" data-toggle="tab">Страница</a>
        </li>
        <li>
            <a href="#template" data-toggle="tab">Шаблон</a>
        </li>
    </ul>
    <form class="form-horizontal route_form" method="POST" action="<?=$addUrl;?>">
        <div class="tab-content with-padding">
            <div class="tab-pane fade in active" id="route">
                <?include(__DIR__ . "/include/route_add.php");?>
            </div>
            <div class="tab-pane fade" id="template">
                <?include(__DIR__ . "/include/template.php");?>
            </div>
            <div class="form-actions text-right">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
$(function(){
    $("form.route_form").on("submit", function(){
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
                                $formItems.find('[name^="route[' + field + ']"]')
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