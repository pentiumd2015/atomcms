<?
use Helpers\CHtml;

?>
<div class="panel-heading">
    <div class="row">
        <div class="col-md-4">
            <?
                if(count($perPageList)){
                    ?>
                        <div class="per_page_title">Показать по:</div>
                        <div class="per_page">
                            <?=CHtml::select($perPageKey, $perPageList, $pagination->perPage, [
                                "id"        => $listId . "_per_page_select",
                                "class"     => "form-control",
                                "onchange"  => CHtml::escape("$.entityDataList(\"" . $listId . "\").refresh();")
                            ]);?>
                        </div>
                    <?
                }
            ?>
        </div>
        <div class="col-md-8 text-right">
            <div class="<?=$listId;?>_pagination" id="<?=$listId;?>_pagination_head">
                <?
                    CWidget::render("pagination", "index", "index", [
                        "pagination"    => $pagination,
                        "urlPageKey"    => $pageKey,
                        "urlPath"       => $baseURL
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>