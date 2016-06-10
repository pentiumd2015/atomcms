<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Шаблоны сайта <small>Список шаблонов</small></h3>
    </div>
</div>

<?
/*filter*/
\Helpers\CBuffer::start();

    foreach($arDisplayList AS $arField){
        $fieldName = $arField["field"];
        
        switch($arField["field"]){
            case "template_id":
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
            <a href="<?=$addURL;?>" class="btn btn-info">Добавить новый шаблон</a>
        </p>
    </div>
</div>
<?
\Helpers\CBuffer::start();
    
    if(count($arTemplates)){
        foreach($arTemplates AS $arTemplate){
            $editLink = str_replace("{ID}", $arTemplate["template_id"], $editURL);
            ?>
                <tr data-id="<?=$arTemplate["template_id"];?>">
                    <td class="text-center">
                        <div class="checkbox checkbox-primary">
                            <?=CHtml::checkbox("checkbox_item[]", false, array(
                                "id"        => "checkbox_item",
                                "value"     => $arTemplate["template_id"]
                            ));?>
                            <label for="checkbox_item"></label>
                        </div>
                    </td>
                    <?
                        foreach($arDisplayList AS $arField){
                            ?>
                                <td>
                                    <?
                                        switch($arField["field"]){
                                            case "template_id":
                                            case "title":
                                                echo '<a href="' . $editLink . '">' . $arTemplate[$arField["field"]] . '</a>';
                                                break;
                                            case "path":
                                                echo str_replace("{TEMPLATE_PATH}", $arTemplate[$arField["field"]], $templatePath);
                                                break;
                                            case "description":
                                                echo $arTemplate[$arField["field"]];
                                                break;
                                        }
                                    ?>
                                </td>
                            <?
                        }
                    ?>
                    <td>
                        <div class="table-controls">
                            <a href="<?=$editLink;?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Изменить" data-toggle="tooltip"><i class="icon-pencil"></i></a>
                            <a href="#" class="btn btn-danger btn-icon btn-xs template_remove"><i class="icon-remove" data-placement="top" title="Удалить" data-toggle="tooltip"></i></a>
                        </div>
                    </td>
                </tr>
            <?
        }
    }
$bodyContent = \Helpers\CBuffer::end();
    
$obAdminTableList->addBodyContent($bodyContent);
echo $obAdminTableList->render();
?>
<div id="template_remove_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
            </div>
            <div class="modal-body with-padding">
                <p>Вы действительно хотите удалить шаблон сайта и все связанные с ним данные?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger template_remove_apply">Удалить</button>
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
    $(document).on("click", ".template_remove", function(e){
        e.preventDefault();
        
        var templateID = $(this).closest("tr").data("id");
        
        $("#template_remove_modal").data("template-id", templateID).modal();
    });
    
    $(document).on("click", ".template_remove_apply", function(e){
        e.preventDefault();
        
        var templateID = $("#template_remove_modal").data("template-id");
        
        if(templateID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget      : "<?=$this->name;?>",
                    method      : "removeTemplate",
                    templateID  : templateID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title   : "Шаблон успешно удален", 
                                theme   : "success",
                                duration: 5000
                            });
                            
                            $("#template_remove_modal").modal("hide");
                            
                            $.tableList("<?=$tableID;?>").refresh();
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Шаблон не был удален", 
                                theme: "error",
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