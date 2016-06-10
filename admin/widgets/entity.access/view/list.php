<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?><small>Редактирование доступа</small></h3>
    </div>
</div>
<div class="tabbable">
    <?
        $arConfig = \CWidget::getConfig();
        
        $obView = new \View\CView;
        
        $obView->setData(array(
            "editURL"   => $entityURL,
            "active"    => "access"
        ));
        
        $tabsPath = "/" . $arConfig["path"] . "entity/" . $arConfig["viewPath"] . "tabs.php";
        
        echo $obView->getContent(ROOT_PATH . $tabsPath);
    ?>
    <form class="form-horizontal entity_access_form" method="POST" action="<?=$listURL;?>">
        <div class="tab-content with-padding">
            <?
                if(count($arErrors)){
                    ?>
                        <div class="alert alert-danger fade in block-inner">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <?
                                $arErrorTmp = array();
                                
                                foreach($arErrors AS $arErrorItems){
                                    foreach($arErrorItems AS $error){
                                        $arErrorTmp[] = $error;
                                    }
                                }
                                
                                echo implode("<br/>", $arErrorTmp);
                            ?>
                        </div>
                    <?
                }
                
                foreach($arUserGroups AS $obUserGroup){
                    $obEntityAccess = $arEntityAccess[$obUserGroup->user_group_id];
                    ?>
                        <div class="form-group<?=($arErrors[$type] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label"><?=$obUserGroup->title;?> [<a href="<?=BASE_URL . "user_groups/" . $obUserGroup->user_group_id . "/";?>">подробнее</a>]:</label>
                            <div class="col-sm-6">
                                <?=CHtml::select("user_group[" . $obUserGroup->user_group_id . "]", $arAccessRuleList, ($obEntityAccess ? $obEntityAccess->access : ""), array(
                                    "class" => "form-control"
                                ));?>
                            </div>
                        </div>
                    <?
                }
            ?>
            <div class="form-actions text-right">
                <button type="submit" class="btn btn-primary">Применить</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function(){
        $("form.entity_access_form").on("submit", function(){
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
                                    $formItems.find('[name^="user_group[' + field + ']"]')
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