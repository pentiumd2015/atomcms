<?
$filterJsPath = $this->path . "js/adminFilter.js";

if(app("request")->isAjax()){
    ?>
        <script type="text/javascript" src="<?=$filterJsPath;?>"></script>
    <?
}else{
    CPage::addJS($filterJsPath);
}
?>
<div id="<?=$arParams["filterID"];?>_wrapper">
    <form<?=CHtml::getAttributeString($arParams["attributes"]);?>>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h6 class="panel-title">Фильтр</h6>
                <?
                    if($arParams["settingsURL"]){
                        $displaySettings = CJSON::encode(array(
                            "url"       => $arParams["settingsURL"],
                            "width"     => 700,
                            "height"    => 400
                        ));
                        ?>
                            <a class="pull-right btn btn-sm btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="#" onclick="(new CModal(<?=CHtml::chars($displaySettings);?>)).show();return false;" title="Настройка отображения">
                                <i class="icon-cogs"></i>
                            </a>
                        <?
                    }
                ?>
            </div>
            <div class="panel-body">
                <?
                    if(count($arParams["fields"])){
                        foreach($arParams["fields"] AS $obField){
                            $fieldName  = $obField->getName();
                            $value      = isset($arParams["filterData"][$fieldName]) ? $arParams["filterData"][$fieldName] : null ;
                                        
                            echo $obField->getRenderer()
                                         ->setParams($arRendererParams)
                                         ->renderFilter($value, $arParams["filterData"]);
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
    $(function(){
        if(typeof $.filterList == "undefined"){
            $.filterList = function(filterID, obj){
                if(filterID){
                    if(typeof obj == "undefined"){
                        return $(document).data("admin-filter-" + filterID);
                    }else{
                        $(document).data("admin-filter-" + filterID, obj)
                        return obj;
                    }
                }
            }
        }
        
        var arParams = <?=CJSON::encode($arParams);?>;
        
        $.filterList(arParams.filterID, new AdminFilter(arParams));
    });
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