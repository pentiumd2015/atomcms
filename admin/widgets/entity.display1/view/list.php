<?
use \Helpers\CHtml;
use \Entities\EntityItem;
use \Helpers\CHttpRequest;
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$obEntity->title;?><small>Отображение разделов и элементов</small></h3>
    </div>
</div>
<div class="callout callout-info fade in" role="alert">
    <h5>Отображение разделов и элементов</h5>
    <p>Каждый пользователь может настроить для себя уникальное отображение данных об элементах или разделах.
        <br />
        При просмотре списка разделов или элементов Вы можете добавлять или изменять столбцы полей, отображаемые в таблице, менять их местами.
        <br />
        При подробном просмотре Вы можете добавлять/изменять вкладки, добавлять во вкладки как сами поля, так и группы полей.
        <br />
        По умолчанию во всех элементах и разделах наследуется отображение, созданное для сущности.
    </p>
</div>
<div class="tabbable">
    <?
        $arConfig = \CWidget::getConfig();
        
        $obView = new \View\CView;
        
        $obView->setData(array(
            "editURL"   => $entityURL,
            "active"    => "display"
        ));
        
        $tabsPath = "/" . $arConfig["path"] . "entity/" . $arConfig["viewPath"] . "tabs.php";
        
        echo $obView->getContent(ROOT_PATH . $tabsPath);
    ?>
    <div class="tab-content with-padding">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal filter_display" method="GET" action="<?=$listURL;?>">
                    <?
                        if($obEntity->use_sections){
                            ?>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Связь: </label>
                                    <div class="col-sm-10">
                                        <?
                                            foreach(EntityItem::$arTypes AS $value => $arRelationItem){
                                                ?>
                                                    <div class="radio radio-inline radio-primary">
                                                        <?=CHtml::radio("entity_display[relation]", ($value == $arFormData["relation"]), array(
                                                            "id"        => "relation_" . $value,
                                                            "value"     => $value
                                                        ));?>
                                                        <label for="relation_<?=$value;?>"><?=$arRelationItem["title"];?></label>
                                                    </div>
                                                <?
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?
                        }
                    ?>
                    <div class="form-group">
                        <label class="control-label col-sm-2">Тип: </label>
                        <div class="col-sm-10">
                            <?
                                $arTypes = array(
                                    "list"      => "Список",
                                    "detail"    => "Подробный просмотр",
                                );
                                
                                foreach($arTypes AS $value => $typeTitle){
                                    ?>
                                        <div class="radio radio-inline radio-primary">
                                            <?=CHtml::radio("entity_display[type]", ($value == $arFormData["type"]), array(
                                                "id"        => "type_" . $value,
                                                "value"     => $value
                                            ));?>
                                            <label for="type_<?=$value;?>"><?=$typeTitle;?></label>
                                        </div>
                                    <?
                                }
                            ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="entity_display_container">
            <div class="row row-margin">
                <div class="col-sm-12">
                    <h5>Общая настройка</h5>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 64px;" class="text-center">ID</th>
                                <th>Сущность</th>
                                <th style="width: 125px;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center"><?=$obEntity->entity_id;?></td>
                                <td><?=$obEntity->title;?></td>
                                <td>
                                    <div class="table-controls">
                                        <?
                                            $editLink = $editURL . "?" . CHttpRequest::toQuery(array(
                                                "relation"    => $arFormData["relation"],
                                                "type"        => $arFormData["type"]
                                            ));
                                        ?>
                                        <a class="btn btn-primary btn-icon btn-xs" href="<?=$editLink;?>">
                                            <i class="icon-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?
                if($obEntity->use_sections && count($arEntitySections)){
                    ?>
                        <div class="row row-margin">
                            <div class="col-sm-12">
                                <h5>Настройка для каждого раздела</h5>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 64px;" class="text-center">ID</th>
                                            <th>Раздел/Сущность</th>
                                            <th style="width: 200px;" class="text-center">Наследование</th>
                                            <th style="width: 125px;">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                            foreach($arEntitySections AS $obEntitySection){
                                                ?>
                                                    <tr data-id="<?=$obEntitySection->entity_item_id;?>" data-relation="<?=$arFormData["relation"]?>" data-type="<?=$arFormData["type"]?>">
                                                        <td class="text-center"><?=$obEntitySection->entity_item_id;?></td>
                                                        <td><?=str_repeat("&nbsp;&nbsp;&nbsp;-", $obEntitySection->depth_level - 1);?><?=$obEntitySection->title;?></td>
                                                        <td>
                                                            <?
                                                                $sectID = $arSectionInheritance[$obEntitySection->entity_item_id];
                                                                if($sectID == 0){
                                                                    ?>
                                                                        <span class="label label-default">Сущность</span>
                                                                    <?
                                                                }else if($sectID == $obEntitySection->entity_item_id){
                                                                    ?>
                                                                        <span class="label label-primary">Своя структура</span>
                                                                    <?
                                                                }else{
                                                                    $editLink = $editURL . "?" . CHttpRequest::toQuery(array(
                                                                        "relation"          => $arFormData["relation"],
                                                                        "type"              => $arFormData["type"],
                                                                        "entity_section_id" => $obEntitySection->entity_item_id
                                                                    ));
                                                                    ?>
                                                                        <span class="label label-info">Раздел [<?=$sectID;?>] <?=$arEntitySections[$sectID]->title;?></span>
                                                                    <?
                                                                }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?
                                                                $editLink = $editURL . "?" . CHttpRequest::toQuery(array(
                                                                    "relation"          => $arFormData["relation"],
                                                                    "type"              => $arFormData["type"],
                                                                    "entity_section_id" => $obEntitySection->entity_item_id
                                                                ));
                                                            ?>
                                                            <div class="table-controls">
                                                                <a class="btn btn-primary btn-icon btn-xs" data-placement="top" title="Настроить" data-toggle="tooltip" href="<?=$editLink;?>">
                                                                    <i class="icon-cogs"></i>
                                                                </a>
                                                                <?
                                                                    if($sectID == $obEntitySection->entity_item_id){
                                                                        ?>
                                                                            <a class="btn btn-danger btn-icon btn-xs entity_display_remove" data-placement="top" title="Сбросить" data-toggle="tooltip">
                                                                                <i class="icon-cancel-circle"></i>
                                                                            </a> 
                                                                        <?
                                                                    }
                                                                ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?
                                            }
                                        ?>
                                    </tbody>
                                </table>
                                <div class="table-footer entity_display_pagination">
                                    <?CWidget::render("pagination", "index", "index", array(
                                        "obPagination"  => $obPagination,
                                        "urlPageKey"    => "page",
                                        "urlPath"       => $listURL
                                    ));?>
                                </div>
                            </div>
                        </div>
                    <?
                }
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){
    function applyFilterForm(){
        var $form = $("form.filter_display");
        
        var arData = $form.find('select, textarea, input[type="text"], input[type="hidden"], input[type="checkbox"]:checked, input[type="radio"]:checked')
                          .filter(function(){
                            return this.value ? true : false; 
                          }).serializeArray();
                                 
        var data = decodeURIComponent($.param(arData));
        
        ajaxRefresh(["#entity_display_container"], {
            data    : data,
            type    : $form.attr("method"),
            url     : $form.attr("action"),
            success : function(){
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }
    
    $("form.filter_display").find('input[type="checkbox"], input[type="radio"]').on("change", applyFilterForm);
    
    $(document).on("click", ".entity_display_remove", function(e){
        e.preventDefault();
        
        var $wrapper    = $(this).closest("tr");
        var data        = $wrapper.data();
        
        if(data.id && data.relation && data.type){
            $.ajax({
                type    : "POST",
                url     : "<?=BASE_URL;?>ajax/",
                data    : {
                    widget          : "entity.display",
                    method          : "removeEntityDisplay",
                    entitySectionID : data.id,
                    relation        : data.relation,
                    type            : data.type
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            $.note({
                                header  : "Успешно!", 
                                title: "Структура успешно удалена", 
                                theme: "success",
                                duration: 5000
                            });
                            
                            ajaxRefresh(["#entity_display_container"], {
                                success : function(){
                                    $('[data-toggle="tooltip"]').tooltip();
                                }
                            });
                        }else{
                            $.note({
                                header  : "Ошибка удаления!", 
                                title: "Структура отображения не была удалена", 
                                theme: "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
        }
    });
    
    $("#entity_display_container").on("click", ".entity_display_pagination li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            ajaxRefresh(["#entity_display_container"], {
                url: url,
                success : function(){
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        }
    });
});
</script>