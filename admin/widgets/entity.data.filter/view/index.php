<?
use Helpers\CHtml;
use Helpers\CJson;
?>
<div id="<?=$filterId;?>_wrapper">
    <form<?=CHtml::getAttributeString($attributes);?>>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h6 class="panel-title">Фильтр</h6>
                <?
                    if($settingsUrl){
                        $displaySettings = CJson::encode([
                            "url"       => $settingsUrl,
                            "width"     => 700,
                            "height"    => 400
                        ]);
                        ?>
                            <a class="pull-right btn btn-sm btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="#" onclick="(new CModal(<?=CHtml::escape($displaySettings);?>)).show();return false;" title="Настройка отображения">
                                <i class="icon-cogs"></i>
                            </a>
                        <?
                    }
                ?>
            </div>
            <div class="panel-body">
                <?
                    if(count($fields)){
                        foreach($fields AS $fieldName => $field){
                            $value = isset($filterData[$fieldName]) ? $filterData[$fieldName] : null ;

                            echo call_user_func_array($field["renderer"], [$value, $filterData, $rendererParams]);
                        }
                        ?>
                            <div class="form-actions text-right">
                                <button type="reset" class="btn btn-xs btn-primary">Отмена</button>
                                <button type="submit" class="btn btn-xs btn-primary">Применить</button>
                            </div>
                        <?
                    }
                ?>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    (function(){
        if(typeof $.entityDataFilter == "undefined"){
            $.entityDataFilter = function(filterId, obj){
                if(filterId){
                    if(typeof obj == "undefined"){
                        return $(document).data("entity-data-filter-" + filterId);
                    }else{
                        $(document).data("entity-data-filter-" + filterId, obj)
                        return obj;
                    }
                }
            }
        }
        
        var params = <?=$jsonParams;?>;
        
        $.entityDataFilter(params.filterId, new EntityDataFilter(params));
    })($);
</script>
<style>
.list_filter_form{
    width: 600px;
}

.list_filter_form .panel{
    margin-bottom: 20px;
}

.list_filter_form .form-group {
    margin-bottom: 10px;
}

.list_filter_form .form-group:first-child{
    margin-top:0;
}

.list_filter_form .help-block{
    margin-top: 2px;
    margin-bottom: 2px;
}
</style>