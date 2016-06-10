<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Сущности <small>Список сущностей</small></h3>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
            <a href="<?=$addURL;?>" class="btn btn-info">Добавить новую сущность</a>
        </p>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h6 class="panel-title">
            <i class="icon-tree"></i> Список сущностей
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Название сущности</th>
                    <th style="width: 125px;">Действие</th>
                </tr>
            </thead>
            <tbody id="entity_list">
                <?
                    if(count($arEntities)){
                        foreach($arEntities AS $obEntity){
                            $editLink = str_replace("{ID}", $obEntity->entity_id, $editURL);
                            ?>
                                <tr data-id="<?=$obEntity->entity_id;?>">
                                    <td><?=$obEntity->entity_id;?></td>
                                    <td>
                                        <a href="<?=$editLink?>"><?=$obEntity->title;?></a>
                                    </td>
                                    <td>
                                        <div class="table-controls">
                                            <a href="<?=$editLink?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Настроить" data-toggle="tooltip"><i class="icon-cogs"></i></a>
                                            <a href="#" class="btn btn-danger btn-icon btn-xs entity_remove" data-placement="top" title="Удалить" data-toggle="tooltip"><i class="icon-remove"></i></a> 
                                        </div>
                                    </td>
                                </tr>
                            <?
                        }
                    }else{
                        ?>
                            <tr>
                                <td colspan="2" class="text-center">У Вас нет ни одной сущности. <a href="<?=$addURL;?>">Добавить сущность</a></td>
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
        <div class="entity_list_pagination">
            <?CWidget::render("pagination", "index", "index", array(
                "obPagination"  => $obPagination,
                "urlPageKey"    => "page",
                "urlPath"       => $listURL
            ));?>
        </div>
    </div>
    
    <div id="entity_remove_modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
                </div>
                <div class="modal-body with-padding">
                    <p>Вы действительно хотите удалить сущность и все связанные с ней записи?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger entity_remove_apply">Удалить</button>
                    <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){       
    $(document).on("click", ".entity_remove", function(e){
        e.preventDefault();
        
        var entityID = $(this).closest("tr").data("id");
        
        $("#entity_remove_modal").data("entity-id", entityID).modal();
    });
    
    $(document).on("click", ".entity_remove_apply", function(e){
        e.preventDefault();
        
        var entityID = $("#entity_remove_modal").data("entity-id");
        
        if(entityID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget  : "<?=$this->name;?>",
                    method  : "removeEntity",
                    entityID: entityID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title: "Сущность успешно удалена", 
                                theme: "success",
                                duration: 5000
                            });
                            
                            ajaxRefresh(["#entity_list", ".entity_list_pagination"], {
                                success: function(){
                                    $("#entity_remove_modal").modal("hide");
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Сущность не была удалена", 
                                theme: "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
        }
    });
    
    $(".entity_list_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            ajaxRefresh(["#entity_list", ".entity_list_pagination"], {
                url: url,
                success: function(){
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        }
    });
});
</script>