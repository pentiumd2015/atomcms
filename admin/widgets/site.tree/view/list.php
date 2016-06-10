<?
use \Helpers\CHtml;
use \Helpers\CHttpRequest;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Дерево сайта <small>Список страниц</small></h3>
    </div>
</div>

<?
/*filter*/
\Helpers\CBuffer::start();

    foreach($arDisplayList AS $arField){
        $fieldName = $arField["field"];
        
        switch($arField["field"]){
            case "path":
            case "title":
                $val = (string)$_REQUEST["f"][$fieldName];
                ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?=$arField["title"]?>:</label>
                        <div class="col-sm-9">
                            <?=CHtml::text("f[" . $fieldName . "]", $val, array(
                                "class" => "form-control input-sm"
                            ));?>
                        </div>
                    </div>
                <?
                break;
            case "active":
                $val = (string)$_REQUEST["f"][$fieldName];
                ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="list_filter_active"><?=$arField["title"]?>:</label>
                        <div class="col-sm-9">
                            <div class="radio radio-inline radio-primary">
                                <?=CHtml::radio("f[" . $fieldName . "]", ($val == 1), array(
                                    "id"    => "list_filter_active_1",
                                    "value" => 1
                                ));?>
                                <label for="list_filter_active_1">Да</label>
                            </div>
                            <div class="radio radio-inline radio-primary">
                                <?=CHtml::radio("f[" . $fieldName . "]", (strlen($val) && $val == 0), array(
                                    "id"    => "list_filter_active_0",
                                    "value" => 0
                                ));?>
                                <label for="list_filter_active_0">Нет</label>
                            </div>
                            <div class="radio radio-inline radio-primary">
                                <?=CHtml::radio("f[" . $fieldName . "]", (strlen($val) == 0), array(
                                    "id"    => "list_filter_active",
                                    "value" => ""
                                ));?>
                                <label for="list_filter_active">Не важно</label>
                            </div>
                        </div>
                    </div>
                <?
                break;
            case "site_id":
                $arSites = CSite::getList();

                $val = (array)$_REQUEST["f"][$fieldName];
                
                $arSiteOptionsList = array("" => "Не выбран");
               
                foreach($arSites AS $arSite){
                    $arSiteOptionsList[$arSite["site_id"]] = $arSite["title"];
                }
                ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?=$arField["title"]?>: </label>
                        <div class="col-sm-9">
                            <?=CHtml::select("f[" . $fieldName . "]", $arSiteOptionsList, $val, array(
                                "class" => "form-control"
                            ));?>
                        </div>
                    </div>
                <?
                break;
        }
    }

$bodyContent = \Helpers\CBuffer::end();

$obAdminTableListFilter->addBodyContent($bodyContent);

echo $obAdminTableListFilter->render();
/*filter*/
?>

<div class="row">
    <div class="col-md-12">
        <p>
            <a href="<?=$addURL;?>" class="btn btn-info">Добавить новую страницу</a>
        </p>
    </div>
</div>
<?
\Helpers\CBuffer::start();
    
    if(count($arSiteRoutes)){
        foreach($arSiteRoutes AS $siteID => $arRoutes){
            if($arSites[$siteID] && count($arRoutes)){
                ?>
                    <tr>
                        <td colspan="20">
                            <a href="<?="/admin/settings/site/" . $arSites[$siteID]["site_id"] . "/"?>" class="label label-primary">Сайт: [<?=$arSites[$siteID]["site_id"];?>] <?=$arSites[$siteID]["title"];?></a>
                        </td>
                    </tr>
                <?
                foreach($arRoutes AS $arRoute){
                    $editLink = $editURL . "?" . CHttpRequest::toQuery(array(
                        "site_id"   => urlencode($siteID), 
                        "path"      => urlencode($arRoute["path"])
                    ));
                    ?>
                        <tr data-id="<?=$siteID . "#" . $arRoute["path"];?>">
                            <td class="text-center">
                                <div class="checkbox checkbox-primary">
                                    <?=CHtml::checkbox("checkbox_item[]", false, array(
                                        "id"        => "checkbox_item",
                                        "value"     => $siteID . "#" . $arRoute["path"]
                                    ));?>
                                    <label for="checkbox_item"></label>
                                </div>
                            </td>
                            <?
                                foreach($arDisplayList AS $arField){
                                    switch($arField["field"]){
                                        case "title":
                                        case "path":
                                            echo '<td><a href="' . $editLink . '">' . $arRoute[$arField["field"]] . '</a></td>';
                                            break;
                                        case "active":
                                            echo "<td>" . ($arRoute[$arField["field"]] ? '<span class="label label-success">Да</span>' : '<span class="label label-warning">Нет</span>') . "</td>" ;
                                            break;
                                    }
                                }
                            ?>
                            <td>
                                <div class="table-controls">
                                    <a href="<?=$editLink;?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Изменить" data-toggle="tooltip"><i class="icon-pencil"></i></a>
                                    <a href="#" class="btn btn-danger btn-icon btn-xs route_remove"><i class="icon-remove" data-placement="top" title="Удалить" data-toggle="tooltip"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?
                }
                
            }
        }
    }
$bodyContent = \Helpers\CBuffer::end();
    
$obAdminTableList->addBodyContent($bodyContent);
echo $obAdminTableList->render();
?>
<div id="route_remove_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
            </div>
            <div class="modal-body with-padding">
                <p>Вы действительно хотите удалить страницу сайта и все связанные с ней данные?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger route_remove_apply">Удалить</button>
                <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function onApplyFilter(arUrlParams, arParams){
    $.tableList("<?=$tableID;?>").refresh(arUrlParams);
}

function onApplyTableList(){
    $('[data-toggle="tooltip"]').tooltip();
    
    $("#<?=$tableID;?>_filter").find(".form-actions button.btn-spin").removeClass("btn-spin");
}

$(function(){
    $(document).on("click", ".route_remove", function(e){
        e.preventDefault();
        
        var routeID = $(this).closest("tr").data("id");
        
        $("#route_remove_modal").data("route-id", routeID).modal();
    });
    
    $(document).on("click", ".route_remove_apply", function(e){
        e.preventDefault();
        
        var routeID = $("#route_remove_modal").data("route-id");
        
        if(routeID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget      : "<?=$this->name;?>",
                    method      : "removeRoute",
                    routeID     : routeID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title   : "Страница успешно удалена", 
                                theme   : "success",
                                duration: 5000
                            });
                            
                            $("#route_remove_modal").modal("hide");
                            
                            $.tableList("<?=$tableID;?>").refresh();
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title   : "Страница не была удалена", 
                                theme   : "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
        }
    });
});
</script>