<?
use \Helpers\CHtml;
use \Models\UserGroupAccess;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Правила доступа <small>Список правил</small></h3>
    </div>
</div>
<?
/*filter*/
\Helpers\CBuffer::start();

    foreach($arDisplayList AS $arField){
        $fieldName = $arField["field"];
        
        switch($arField["field"]){
            case "user_group_access_id":
                $val = (string)$_REQUEST["f"][$fieldName];
                ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?=$arField["title"]?>:</label>
                        <div class="col-sm-9">
                            <?=CHtml::text("f[" . $fieldName . "]", $val, array(
                                "class" => "form-control input-sm"
                            ));?>
                            <span class="help-block">пример: 15 ; 1-69 ; 1,56 ; -54 ; 126-</span>
                        </div>
                    </div>
                <?
                break;
            case "title":
            case "alias":
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
            <a href="<?=$addURL;?>" class="btn btn-info">Добавить новое правило</a>
        </p>
    </div>
</div>
<?
\Helpers\CBuffer::start();
    if(count($arUserGroupAccess)){
        foreach($arUserGroupAccess AS $arUserGroupAccess){
            $editLink = str_replace("{ID}", $arUserGroupAccess["user_group_access_id"], $editURL);
            ?>
                <tr data-id="<?=$arUserGroupAccess["user_group_access_id"];?>">
                    <td class="text-center">
                        <div class="checkbox checkbox-primary">
                            <?
                                if($arUserGroupAccess["alias"] != UserGroupAccess::ADMIN_ACCESS){
                                    echo CHtml::checkbox("checkbox_item[]", false, array(
                                        "id"        => "checkbox_item",
                                        "value"     => $arUserGroupAccess["user_group_access_id"]
                                    ));
                                }else{
                                    echo CHtml::checkbox("checkbox_item[]", false, array(
                                        "id"        => "checkbox_item",
                                        "value"     => $arUserGroupAccess["user_group_access_id"],
                                        "disabled"  => "disabled"
                                    ));
                                }
                            ?>
                            <label for="checkbox_item"></label>
                        </div>
                    </td>
                    <?
                        foreach($arDisplayList AS $arField){
                            ?>
                                <td>
                                    <?
                                        switch($arField["field"]){
                                            case "user_group_access_id":
                                            case "title":
                                                echo '<a href="' . $editLink . '">' . $arUserGroupAccess[$arField["field"]] . '</a>';
                                                break;
                                            case "alias":
                                                echo $arUserGroupAccess[$arField["field"]];
                                                break;
                                            case "description":
                                                echo $arUserGroupAccess[$arField["field"]];
                                                break;
                                        }
                                    ?>
                                </td>
                            <?
                        }
                    ?>
                    <td>
                        <?
                            if($arUserGroupAccess["alias"] != UserGroupAccess::ADMIN_ACCESS){
                                ?>
                                    <div class="table-controls">
                                        <a href="<?=$editLink;?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Изменить" data-toggle="tooltip"><i class="icon-pencil"></i></a>
                                        <a href="#" class="btn btn-danger btn-icon btn-xs user_group_access_remove"><i class="icon-remove" data-placement="top" title="Удалить" data-toggle="tooltip"></i></a> 
                                    </div>
                                <?
                            }
                        ?>
                    </td>
                </tr>
            <?
        }
    }
    
$bodyContent = \Helpers\CBuffer::end();
    
$obAdminTableList->addBodyContent($bodyContent);
echo $obAdminTableList->render();
?>
<div id="user_group_access_remove_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
            </div>
            <div class="modal-body with-padding">
                <p>Вы действительно хотите удалить правило и все связанные с ним данные?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger user_group_access_remove_apply">Удалить</button>
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
    $(document).on("click", ".user_group_access_remove", function(e){
        e.preventDefault();
        
        var userGroupAccessID = $(this).closest("tr").data("id");
        
        $("#user_group_access_remove_modal").data("user-group-access-id", userGroupAccessID).modal();
    });
    
    $(document).on("click", ".user_group_access_remove_apply", function(e){
        e.preventDefault();
        
        var userGroupAccessID = $("#user_group_access_remove_modal").data("user-group-access-id");
        
        if(userGroupAccessID){
            $.ajax({
                type    : "POST",
                url     : "<?=BASE_URL;?>ajax/",
                data    : {
                    widget              : "<?=$this->name;?>",
                    method              : "removeUserGroupAccess",
                    userGroupAccessID   : userGroupAccessID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title   : "Правило успешно удалено", 
                                theme   : "success",
                                duration: 5000
                            });
                            
                            $("#user_group_access_remove_modal").modal("hide");
                            
                            $.tableList("<?=$tableID;?>").refresh();
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Правило не было удалено", 
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