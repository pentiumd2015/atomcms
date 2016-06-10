<div class="panel-heading">
    <div class="row">
        <div class="col-md-4">
            <?
                if(count($arParams["perPageList"])){
                    ?>
                        <div id="<?=$arParams["listID"];?>_per_page_title">Показать по:</div>
                        <div id="<?=$arParams["listID"];?>_per_page">
                            <?=CHtml::select($arParams["perPageKey"], $arParams["perPageList"], $_REQUEST[$arParams["perPageKey"]], [
                                "id"        => $arParams["listID"] . "_per_page_select",
                                "class"     => "form-control",
                                "onchange"  => "$.adminList(&quot;" . $arParams["listID"] . "&quot;).refresh();"
                            ]);?>
                        </div>
                    <?
                }
            ?>
        </div>
        <div class="col-md-8 text-right">
            <?
                if($arParams["pagination"]){
                    ?>
                        <div class="<?=$arParams["listID"];?>_pagination" id="<?=$arParams["listID"];?>_pagination_head">
                            <?
                                CWidget::render("pagination", "index", "index", [
                                    "obPagination"  => $arParams["pagination"],
                                    "urlPageKey"    => $arParams["pageKey"],
                                    "urlPath"       => $arParams["baseURL"]
                                ]);
                            ?>
                        </div>
                    <?
                }
            ?>
        </div>
    </div>
</div>