<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Группы сущностей <small>Список групп</small></h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
            <a href="<?=$addURL;?>" class="btn btn-info">Добавить новую группу</a>
        </p>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h6 class="panel-title">
            <i class="icon-tree"></i> Список групп
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Название группы</th>
                    <th style="width: 125px;">Действие</th>
                </tr>
            </thead>
            <tbody id="entity_group_list">
                <?
                    if(count($arEntityGroups)){
                        foreach($arEntityGroups AS $obEntityGroup){
                            $editLink = str_replace("{ID}", $obEntityGroup->entity_group_id, $editURL);
                            ?>
                                <tr data-id="<?=$obEntityGroup->entity_group_id;?>">
                                    <td><?=$obEntityGroup->entity_group_id;?></td>
                                    <td>
                                        <a href="<?=$editLink;?>"><?=$obEntityGroup->title;?></a>
                                    </td>
                                    <td>
                                        <div class="table-controls">
                                            <a href="<?=$editLink;?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Изменить" data-toggle="tooltip"><i class="icon-pencil"></i></a>
                                            <a href="#" class="btn btn-danger btn-icon btn-xs entity_group_remove"><i class="icon-remove" data-placement="top" title="Удалить" data-toggle="tooltip"></i></a> 
                                        </div>
                                    </td>
                                </tr>
                            <?
                        }
                    }else{
                        ?>
                            <tr>
                                <td colspan="3" class="text-center">У Вас нет ни одной группы. <a href="<?=$addURL;?>">Добавить группу</a></td>
                            </tr>
                        <?
                    }
                ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
      <!--  <div class="table-actions">
            <label>Apply action:</label>
            <select data-placeholder="Select action..." class="select-liquid">
                <option value=""></option>
                <option value="Edit">Edit</option>
                <option value="Move">Move</option>
                <option value="Delete">Delete</option>
            </select>
        </div>-->
        <div class="entity_group_list_pagination">
            <?CWidget::render("pagination", "index", "index", array(
                "obPagination"  => $obPagination,
                "urlPageKey"    => "page",
                "urlPath"       => $listURL
            ));?>
        </div>
    </div>
    
    <div id="entity_group_remove_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
                </div>
                <div class="modal-body with-padding">
                    <p>Вы действительно хотите удалить группу и все связанные с ней данные?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger entity_group_remove_apply">Удалить</button>
                    <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){       
    $(document).on("click", ".entity_group_remove", function(e){
        e.preventDefault();
        
        var entityGroupID = $(this).closest("tr").data("id");
        
        $("#entity_group_remove_modal").data("entity-group-id", entityGroupID).modal();
    });
    
    $(document).on("click", ".entity_group_remove_apply", function(e){
        e.preventDefault();
        
        var entityGroupID = $("#entity_group_remove_modal").data("entity-group-id");
        
        if(entityGroupID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget          : "<?=$this->name;?>",
                    method          : "removeEntityGroup",
                    entityGroupID   : entityGroupID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title   : "Группа успешно удалена", 
                                theme   : "success",
                                duration: 5000
                            });
                            
                            ajaxRefresh(["#entity_group_list", ".entity_group_list_pagination"], {
                                success: function(){
                                    $("#entity_group_remove_modal").modal("hide");
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Группа не была удалена", 
                                theme: "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
        }
    });
    
    $(".entity_group_list_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            ajaxRefresh(["#entity_group_list", ".entity_group_list_pagination"], {
                url: url,
                success: function(){
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        }
    });
});
</script>