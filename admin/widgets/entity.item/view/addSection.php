<div class="page-header">
    <div class="page-title">
        <h3>Новый раздел <small>Добавление</small></h3>
    </div>
</div>
<?
    if(count($arErrors)){
        ?>
            <div class="alert alert-danger fade in block-inner">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <?
                    $arErrorTmp = array();
                    
                    foreach($arErrors AS $key => $arErrorItems){
                        if($key == "fields"){
                            foreach($arErrorItems AS $fieldID => $arErrorFields){
                                foreach($arErrorFields AS $errorField){
                                    $arErrorTmp[] = "Поле [" . $arEntityFields[$fieldID]->title . "]: " . $errorField;
                                }
                            }
                        }else{
                            foreach($arErrorItems AS $error){
                                $arErrorTmp[] = $error;
                            }
                        }
                    }
                    
                    echo implode("<br/>", $arErrorTmp);
                ?>
            </div>
        <?
    }
?>
<div class="tabbable item_tabs_container">
    <ul class="nav nav-tabs">
         <?
            foreach($arEntityDisplay AS $tabIndex => $arViewTab){
                $activeTab = ($tabIndex == 0);
                ?>
                    <li<?=($activeTab ? ' class="active"' : "");?>>
                        <a href="#view_tab_<?=($tabIndex + 1);?>" data-toggle="tab"><?=$arViewTab["title"];?></a>
                    </li>
                <?
            }
        ?>
        <li class="nav-group-button">
            <div>
                <a class="btn btn-primary btn-icon" data-placement="top" title="Настройки" data-toggle="tooltip" href="<?=$displaySettingsURL;?>">
                    <i class="icon-cog4"></i>
                    <div class="clearfix"></div>
                </a>
            </div>
        </li>
    </ul>
    <form class="form-horizontal entity_item_form" method="POST" action="<?=$addSectionURL;?>">
        <div class="tab-content with-padding">
           <?
                foreach($arEntityDisplay AS $tabIndex => $arViewTab){
                    $activeTab = ($tabIndex == 0);
                    ?>
                        <div class="tab-pane fade<?=($activeTab ? " active in" : "");?>" id="view_tab_<?=($tabIndex + 1);?>">
                            <?
                                if($arViewTab["items"]){
                                    foreach($arViewTab["items"] AS $itemIndex => $arTabItem){
                                        if($arTabItem["type"] == "field"){ // field
                                            if($arTabItem["isBase"]){
                                                $fieldName  = $arTabItem["field"];
                                                $obField    = $arBaseFields[$fieldName];
                                                
                                                if($obField){
                                                    echo $obField->renderDetail();
                                                }
                                            }else{
                                                $fieldID = $arTabItem["field"];
                                                $obField = $arEntityFields[$fieldID];
                                                
                                                if($obField){
                                                    echo $obField->obFieldType->renderDetail();
                                                }
                                            }
                                        }else if($arTabItem["type"] == "group"){ //group field
                                            ?>
                                                <div class="panel-group block">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h6 class="panel-title panel-trigger active">
                                                                <a data-toggle="collapse" href="#view_group_<?=($tabIndex + 1);?>_<?=($itemIndex + 1);?>"><?=$arTabItem["title"]?></a>
                                                            </h6>
                                                        </div>
                                                        <div id="view_group_<?=($tabIndex + 1);?>_<?=($itemIndex + 1);?>" class="panel-collapse collapse in">
                                                            <div class="panel-body">
                                                                <?
                                                                    if($arTabItem["items"]){
                                                                        foreach($arTabItem["items"] AS $arTabField){ //fields
                                                                            if($arTabField["isBase"]){
                                                                                $fieldName  = $arTabField["field"];
                                                                                $obField    = $arBaseFields[$fieldName];
                                                                                
                                                                                if($obField){
                                                                                    echo $obField->renderDetail();
                                                                                }
                                                                            }else{
                                                                                $fieldID = $arTabField["field"];
                                                                                $obField = $arEntityFields[$fieldID];
                                                                                
                                                                                if($obField){
                                                                                    echo $obField->obFieldType->renderDetail();
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?
                                        }
                                    }
                                }
                            ?>
                        </div>
                    <?
                }
            ?>
            <div class="form-actions text-right">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
$(function(){
    $("form.entity_item_form").on("submit", function(){
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
                            
                            if(r.errors){
                                for(var field in r.errors){
                                    $formItems.find('[name^="entity_item[' + field + ']"]')
                                              .closest(".form-group")
                                              .addClass("has-error");
                                }
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