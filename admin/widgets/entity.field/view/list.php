<?
    use \Helpers\CHtml;
    use \Entities\EntityItem;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?><small>Список полей</small></h3>
    </div>
</div>
<?/*
<div class="callout callout-info fade in" role="alert">
    <h5>Поля сущности</h5>
    <p>Для добавления дополнительной информации, в системе есть возможность добавить поля. 
    <br/>Поля могут быть типа текст, число, файл и другие. 
    <br/>Поля можно создать для разделов и/или для элементов сущности.
    <br/>Разделы предназначены для группировки элементов в рамках одной сущности.
    <br/>Например, для сущности "Каталог" с товарами, элементом будет товар "Телевизор Sony VM332", а разделом элемента будет "Телевизоры"
    </p>
</div>
*/?>
<div class="tabbable">
    <?
        $arConfig = \CWidget::getConfig();
        
        $obView = new \View\CView;
        
        $obView->setData(array(
            "editURL"   => $entityURL,
            "active"    => "fields"
        ));
        
        $tabsPath = "/" . $arConfig["path"] . "entity/" . $arConfig["viewPath"] . "tabs.php";
        
        echo $obView->getContent(ROOT_PATH . $tabsPath);
    ?>
    <div class="tab-content with-padding">
        <div class="row">
            <div class="col-sm-12">
                <p>
                    <a href="<?=$addURL;?>" class="btn btn-info">Добавить новое поле</a>
                </p>
            </div>
            <?
                if($obEntity->use_sections){
                    ?>
                        <div class="col-sm-5">
                            <form class="form-horizontal filter_field" method="GET" action="<?=$listURL;?>">
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Связь: </label>
                                    <div class="col-sm-10">
                                        <?
                                            foreach(EntityItem::$arTypes AS $type => $arFieldRelation){
                                                ?>
                                                    <div class="radio radio-inline radio-primary">
                                                        <?=CHtml::radio("filter_field[relation]", ($type == $arFormData["relation"]), array(
                                                            "id"        => "type_" . $type,
                                                            "value"     => $type
                                                        ));?>
                                                        <label for="type_<?=$type;?>"><?=$arFieldRelation["title"];?></label>
                                                    </div>
                                                <?
                                            }
                                        ?>
                                    </div>
                                </div>
                                
                            </form>
                        </div>
                    <?
                }
            ?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Название поля</th>
                        <th>Тип</th>
                        <th>Описание</th>
                        <th class="text-center" style="width: 120px;">Обязатальное</th>
                        <th class="text-center" style="width: 120px;">Уникальное</th>
                        <th class="text-center" style="width: 125px;">Действие</th>
                    </tr>
                </thead>
                <tbody id="entity_field_list">
                    <?
                        if(count($arEntityFields)){
                            foreach($arEntityFields AS $obEntityField){
                                $editLink = str_replace("{FIELD_ID}", $obEntityField->entity_field_id, $editURL);
                                ?>
                                    <tr data-id="<?=$obEntityField->entity_field_id;?>">
                                        <td><?=$obEntityField->entity_field_id;?></td>
                                        <td>
                                            <a href="<?=$editLink?>"><?=$obEntityField->title;?></a>
                                        </td>
                                        <td><?=(isset($arFieldTypes[$obEntityField->type]) ? $arFieldTypes[$obEntityField->type]["title"] : "");?></td>
                                        <td><?=$obEntityField->description;?></td>
                                        <td class="text-center"><?=($obEntityField->is_required ? "Да" : "Нет");?></td>
                                        <td class="text-center"><?=($obEntityField->is_unique ? "Да" : "Нет");?></td>
                                        <td>
                                            <div class="table-controls">
                                                <a href="<?=$editLink?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Настроить" data-toggle="tooltip"><i class="icon-cogs"></i></a>
                                                <a href="#" class="btn btn-danger btn-icon btn-xs entity_field_remove" data-placement="top" title="Удалить" data-toggle="tooltip"><i class="icon-remove"></i></a> 
                                            </div>
                                        </td>
                                    </tr>
                                <?
                            }
                        }else{
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center">У Вас нет ни одного поля. <a href="<?=$addURL;?>">Добавить поле</a></td>
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
            <div class="entity_field_list_pagination">
                <?CWidget::render("pagination", "index", "index", array(
                    "obPagination"  => $obPagination,
                    "urlPageKey"    => "page",
                    "urlPath"       => $listURL
                ));?>
            </div>
        </div>
        
        <div id="entity_field_remove_modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
                    </div>
                    <div class="modal-body with-padding">
                        <p>Вы действительно хотите удалить поле и все связанные с ним записи?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger entity_field_remove_apply">Удалить</button>
                        <button class="btn btn-primary" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){
    function applyFilterForm(){
        var $form = $("form.filter_field");
        
        var arData = $form.find('select, textarea, input[type="text"], input[type="hidden"], input[type="checkbox"]:checked, input[type="radio"]:checked')
                          .filter(function(){
                            return this.value ? true : false; 
                          }).serializeArray();
                                            
        var data = decodeURIComponent($.param(arData));
        
        ajaxRefresh(["#entity_field_list", ".entity_field_list_pagination"], {
            data    : data,
            type    : $form.attr("method"),
            url     : $form.attr("action"),
            success : function(){
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }
    
    $('input[name="filter_field[relation]"]').on("change", applyFilterForm);
    
    $(document).on("click", ".entity_field_remove", function(e){
        e.preventDefault();
        
        var entityFieldID = $(this).closest("tr").data("id");
        
        $("#entity_field_remove_modal").data("entity-field-id", entityFieldID).modal();
    });
    
    $(document).on("click", ".entity_field_remove_apply", function(e){
        e.preventDefault();
        
        var entityFieldID = $("#entity_field_remove_modal").data("entity-field-id");
        
        if(entityFieldID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget          : "entity.field",
                    method          : "removeEntityField",
                    entityFieldID   : entityFieldID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title: "Поле успешно удалено", 
                                theme: "success",
                                duration: 5000
                            });
                            
                            ajaxRefresh(["#entity_field_list", ".entity_field_list_pagination"], {
                                success: function(){
                                    $("#entity_field_remove_modal").modal("hide");
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Поле не было удалено", 
                                theme: "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
        }
    });
    
    $(".entity_field_list_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            ajaxRefresh(["#entity_field_list", ".entity_field_list_pagination"], {
                url: url,
                success: function(){
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        }
    });
});
</script>