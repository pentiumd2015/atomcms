<?
use \Helpers\CHtml;
?>
<div class="page-header">
    <div class="page-title">
        <h3>Список сущностей</h3>
    </div>
</div>
<div class="panel panel-default">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Название сущности</th>
                </tr>
            </thead>
            <tbody id="entity_list">
                <?
                    if(count($arEntities)){
                        foreach($arEntities AS $obEntity){
                            ?>
                                <tr data-id="<?=$obEntity->entity_id;?>">
                                    <td><?=$obEntity->entity_id;?></td>
                                    <td>
                                        <a href="<?=str_replace("{ENTITY_ID}", $obEntity->entity_id, $listElementURL);?>"><?=$obEntity->title;?></a>
                                    </td>
                                </tr>
                            <?
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="entity_list_pagination">
            <?CWidget::render("pagination", "index", "index", array(
                "obPagination"  => $obPagination,
                "urlPageKey"    => "page",
                "urlPath"       => $listURL
            ));?>
        </div>
    </div>
</div>
<script type="text/javascript">
$(function(){
    $(".entity_list_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            ajaxRefresh(["#entity_list", ".entity_list_pagination"], {
                url: url,
                success: function(){
                    
                }
            });
        }
    });
});
</script>