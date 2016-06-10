<?
use \Helpers\CHtml;
use \Entities\Entity;

$arListParams = $obAdminTableList->getParams();
$tableID = $arListParams["tableID"];
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?></h3>
    </div>
</div>
<?
/*filter*/
\Helpers\CBuffer::start();

    foreach($arEntityDisplayFilter AS $arField){
        if($arField["isBase"]){
            $fieldName = $arField["field"];
            
            $obField = $arBaseFields[$fieldName];
            
            if($obField){
                $obField->setData(array(
                    "value" => $_REQUEST["f"][$fieldName]
                ));
                echo $obField->renderFilter();
            }
        }else{
            $fieldID = $arField["field"];
            $obField = $arEntityFields[$fieldID];
            
            if($obField){
                $obField->obFieldType->setData(array(
                    "value" => $_REQUEST["f"]["f_" . $fieldID]
                ));
                echo $obField->obFieldType->renderFilter();
            }
        }
    }

$bodyContent = \Helpers\CBuffer::end();

$obAdminTableListFilter->addBodyContent($bodyContent);

echo $obAdminTableListFilter->render();
/*filter*/

?>
    <div class="row">
        <div class="col-md-6">
            <a class="btn btn-info" href="<?=$addElementURL;?>">
                <i class="icon-plus"></i>
                <?=$obEntity->params["signatures"][Entity::SIGNATURE_ADD_ELEMENT]["title"];?>
            </a>
            <a class="btn btn-primary" href="<?=$addSectionURL;?>">
                <i class="icon-plus"></i>
                <?=$obEntity->params["signatures"][Entity::SIGNATURE_ADD_SECTION]["title"];?>
            </a>
            <a class="btn btn-primary" href="<?=$listElementURL;?>">
                <i class="icon-tree"></i>
                <?=$obEntity->params["signatures"][Entity::SIGNATURE_ELEMENTS]["title"];?>
            </a>
        </div>
        <div class="col-md-6 text-right">
            <a class="btn btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="<?=$displaySettingsURL;?>" title="Настройки">
                <i class="icon-cog4"></i>
            </a>
        </div>
    </div>
<?

/*list items*/

    
\Helpers\CBuffer::start();

    if(count($arEntitySections)){
        foreach($arEntitySections AS $obEntitySection){
            $itemURL = str_replace("{ID}", $obEntitySection->entity_item_id, $editSectionURL);
            ?>
                <tr data-id="<?=$obEntitySection->entity_item_id;?>">
                    <td class="text-center">
                        <div class="checkbox checkbox-primary">
                            <?=CHtml::checkbox("checkbox_item[]", false, array(
                                "id"    => "checkbox_item",
                                "value" => $obEntitySection->entity_item_id
                            ));?>
                            <label for="checkbox_item"></label>
                        </div>
                    </td>
                    <?
                        foreach($arEntityDisplayList AS $arField){
                            if($arField["isBase"]){
                                $fieldName = $arField["field"];
                                
                                $obField = $arBaseFields[$fieldName];
                                
                                if($obField){
                                    $obField->setData(array(
                                        "itemURL"   => $itemURL,
                                        "arItem"    => (array)$obEntitySection
                                    ));
                                    ?>
                                        <td><?=$obField->renderList();?></td>
                                    <?
                                }else{
                                    ?>
                                        <td></td>
                                    <?
                                }
                            }else{
                                $fieldID = $arField["field"];
                                $obField = $arEntityFields[$fieldID];
                                
                                if($obField){
                                    $obField->obFieldType->setData(array(
                                        "itemURL"   => $itemURL,
                                        "arItem"    => (array)$obEntitySection
                                    ));
                                    ?>
                                        <td><?=$obField->obFieldType->renderList();?></td>
                                    <?
                                }else{
                                    ?>
                                        <td></td>
                                    <?
                                }
                            }
                        }
                    ?>
                    <td>
                        <div class="table-controls">
                            <a href="<?=$itemURL?>" class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Изменить" data-toggle="tooltip"><i class="icon-pencil"></i></a>
                            <a href="#" class="btn btn-danger btn-icon btn-xs entity_item_remove" data-placement="top" title="Удалить" data-toggle="tooltip"><i class="icon-remove"></i></a> 
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

<div id="entity_item_remove_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="icon-remove"></i> Подтверждение удаления</h4>
            </div>
            <div class="modal-body with-padding">
                <p>Вы действительно хотите удалить элемент и все связанные с ним записи?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger entity_item_remove_apply">Удалить</button>
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
    $(document).on("click", ".entity_item_remove", function(e){
        e.preventDefault();
        
        var entityElementID = $(this).closest("tr").data("id");
        
        $("#entity_item_remove_modal").data("entity-element-id", entityElementID).modal();
    });
    
    $(document).on("click", ".entity_item_remove_apply", function(e){
        e.preventDefault();
        
        var entityElementID = $("#entity_item_remove_modal").data("entity-element-id");
        
        if(entityElementID){
            $.ajax({
                type    : "POST",
                url     : "/admin/ajax/",
                data    : {
                    widget          : "entity.item",
                    method          : "removeEntityElement",
                    entityElementID : entityElementID
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title: "Элемент успешно удален",
                                theme: "success",
                                duration: 5000
                            });
                            
                            $("#entity_item_remove_modal").modal("hide");
                            
                            $.tableList("<?=$tableID;?>").refresh();
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Элемент не был удален", 
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