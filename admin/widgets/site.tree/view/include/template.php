<?
use \Helpers\CHtml;
use \Helpers\CJSON;
?>
<div class="row">
    <div class="col-sm-12">
        <p>
            <a href="#" class="btn btn-primary" id="add_template_item">Добавить шаблон</a>
        </p>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <ul class="panel-group block templates_list" id="templates_container">
            <?
                $index = 0;
                if(is_array($arFormData["templates"])){
                    foreach($arFormData["templates"] AS $arTemplateItem){
                        ?>
                            <li class="panel panel-info template_item">
                                <div class="panel-heading">
                                    <h6 class="panel-title panel-trigger">
                                        <div class="drag_handle">
                                            <i class="icon-move"></i>
                                        </div>
                                        <a data-toggle="collapse" href="#collapse-template<?=$index;?>"><?=("Шаблон [" . $arTemplateItem["template"] . "], Слой: [" . $arTemplateLayouts[$arTemplateItem["template"]][$arTemplateItem["layoutFile"]] . "], Страница: [" . $arTemplatePages[$arTemplateItem["template"]][$arTemplateItem["pageFile"]] . "]")?></a>
                                    </h6>
                                    <div class="pull-right">
                                        <a href="#" class="delete_template_item">
                                            <i class="icon icon-close panel-icon panel-icon-sm panel-icon-white"></i>
                                        </a>
                                    </div>
                                </div>
                                <div id="collapse-template<?=$index;?>" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Активность:</label>
                                            <div class="col-sm-6">
                                                <div class="checkbox checkbox-primary">
                                                    <?=CHtml::boolean("route[templates][" . $index . "][active]", array(1, 0), $arTemplateItem["active"], array(
                                                        "id" => "is_active"
                                                    ));?>
                                                    <label for="is_active"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Шаблон:<span class="mandatory">*</span></label>
                                            <div class="col-sm-6">
                                                <?=CHtml::select("route[templates][" . $index . "][template]", $arTemplatesOptionList, $arTemplateItem["template"], array(
                                                    "class" => "form-control template_id"
                                                ));?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Файл слоя:</label>
                                            <div class="col-sm-6">
                                                <?=CHtml::select("route[templates][" . $index . "][layoutFile]", $arTemplateLayouts[$arTemplateItem["template"]], $arTemplateItem["layoutFile"], array(
                                                    "class" => "form-control layout_file"
                                                ));?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Файл страницы:</label>
                                            <div class="col-sm-6">
                                                <?=CHtml::select("route[templates][" . $index . "][pageFile]", $arTemplatePages[$arTemplateItem["template"]], $arTemplateItem["pageFile"], array(
                                                    "class" => "form-control page_file"
                                                ));?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Использовать условие:</label>
                                            <div class="col-sm-6">
                                                <div class="checkbox checkbox-primary">
                                                    <?=CHtml::boolean("route[templates][" . $index . "][use_condition]", array(1, 0), $arTemplateItem["use_condition"], array(
                                                        "id"    => "use_condition" . $index,
                                                        "class" => "use_condition"
                                                    ));?>
                                                    <label for="use_condition<?=$index;?>"></label>
                                                </div>
                                                <span class="help-block">При включенном условии шаблон будет срабатывать только тогда, когда выполняется условие</span>
                                            </div>
                                        </div>
                                        <div class="form-group condition_container"<?=($arTemplateItem["use_condition"] ? "" : 'style="display:none;"')?>>
                                            <label class="col-sm-2 control-label">Условие:</label>
                                            <div class="col-sm-6">
                                                <?=CHtml::textarea("route[templates][" . $index . "][condition]", $arTemplateItem["condition"], array(
                                                    "class" => "form-control"
                                                ));?>
                                                <span class="help-block">Условие должно возвращать true, либо false</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?
                        $index++;
                    }
                }
            ?>
        </ul>
    </div>
</div>
<style>
.templates_list{
    padding: 0;
    list-style: none;
}

.drag_handle{
    float: left;
    padding: 8px 12px;
    cursor:pointer;
}

.templates_list .panel-trigger > a { 
  padding: 10px 14px 10px 70px ;
}

.templates_list .panel-trigger > a:after{
    left: 48px;
}

.templates_list .panel-trigger.active{
    color:#fff;
}
</style>
<script type="text/template" id="content_template_item_template">
    <?$templateID = key($arTemplatesOptionList);?>
    <li class="panel panel-info template_item">
        <div class="panel-heading">
            <h6 class="panel-title panel-trigger active">
                <div class="drag_handle">
                    <i class="icon-move"></i>
                </div>
                <a data-toggle="collapse" href="#collapse-template#index#">Новый шаблон</a>
            </h6>
            <div class="pull-right">
                <a href="#" class="delete_template_item">
                    <i class="icon icon-close panel-icon panel-icon-sm panel-icon-white"></i>
                </a>
            </div>
        </div>
        <div id="collapse-template#index#" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Активность:</label>
                    <div class="col-sm-6">
                        <div class="checkbox checkbox-primary">
                            <?=CHtml::boolean("route[templates][#index#][active]", array(1, 0), 0, array(
                                "id" => "is_active"
                            ));?>
                            <label for="is_active"></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Шаблон:<span class="mandatory">*</span></label>
                    <div class="col-sm-6">
                        <?=CHtml::select("route[templates][#index#][template]", $arTemplatesOptionList, "", array(
                            "class" => "form-control template_id"
                        ));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Файл слоя:</label>
                    <div class="col-sm-6">
                        <?=CHtml::select("route[templates][#index#][layoutFile]", $arTemplateLayouts[$templateID], "", array(
                            "class" => "form-control layout_file"
                        ));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Файл страницы:</label>
                    <div class="col-sm-6">
                        <?=CHtml::select("route[templates][#index#][pageFile]", $arTemplatePages[$templateID], "", array(
                            "class" => "form-control page_file"
                        ));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Использовать условие:</label>
                    <div class="col-sm-6">
                        <div class="checkbox checkbox-primary">
                            <?=CHtml::boolean("route[templates][#index#][use_condition]", array(1, 0), 0, array(
                                "id"    => "use_condition#index#",
                                "class" => "use_condition"
                            ));?>
                            <label for="use_condition#index#"></label>
                        </div>
                        <span class="help-block">При включенном условии шаблон будет срабатывать только тогда, когда выполняется условие</span>
                    </div>
                </div>
                <div class="form-group condition_container" style="display:none;">
                    <label class="col-sm-2 control-label">Условие:</label>
                    <div class="col-sm-6">
                        <?=CHtml::textarea("route[templates][#index#][condition]", "", array(
                            "class" => "form-control"
                        ));?>
                        <span class="help-block">Условие должно возвращать true, либо false</span>
                    </div>
                </div>
            </div>
        </div>
    </li>
</script>

<script type="text/javascript">
    $(function(){
        function refreshDraggable(){
            $("#templates_container").sortable({
                handle      : ".drag_handle",
                placeholder : "display_placeholder",
                start       : function(event, ui){
                    var h = ui.item.outerHeight(true);
                    ui.placeholder.css({height: h});
                },
            }).disableSelection();
        }
        
        refreshDraggable();
        
        var maxIndex            = <?=$index;?>;
        var arTemplateLayouts   = <?=CJSON::encode($arTemplateLayouts);?>;
        var arTemplatePages     = <?=CJSON::encode($arTemplatePages);?>;
        
        $(document).on("click", ".panel-trigger", function(e){
    		e.preventDefault();
    		$(this).toggleClass("active");
    	});
        
        $(document).on("click", ".delete_template_item", function(e){
            e.preventDefault();
            
            $(this).closest(".template_item").remove();
        });
        
        $("#add_template_item").on("click", function(e){
            e.preventDefault();
            
            var templateItemHtml = getTemplate("#content_template_item_template", {
                index: maxIndex
            });
            
            maxIndex++;
            
            $("#templates_container").append(templateItemHtml);
        });
    
        $(document).on("change", ".template_id", function(){
            var templateID  = $(this).val();
            var $wrapper    = $(this).closest(".template_item");
            
            /*layouts*/
            var arLayouts = arTemplateLayouts[templateID];
            
            var options = "";
            
            if(arLayouts){
                for(var i in arLayouts){
                    options+= "<option value=\"" + escapeHtml(i) + "\">" + arLayouts[i] + "</option>";
                }
            }
            
            $wrapper.find(".layout_file").html(options);
            /*layouts*/
            
            /*pages*/
            var arPages = arTemplatePages[templateID];
            
            var options = "";
            
            if(arPages){
                for(var i in arPages){
                    options+= "<option value=\"" + escapeHtml(i) + "\">" + arPages[i] + "</option>";
                }
            }
            
            $wrapper.find(".page_file").html(options);
            /*pages*/
        });
        
        $(document).on("change", ".use_condition", function(){
            var $wrapper = $(this).closest(".template_item");
            
            $wrapper.find(".condition_container")[($(this).is(":checked") ? "show" : "hide")]();
        });
    });
</script>
