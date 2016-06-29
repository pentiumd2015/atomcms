<?
use Helpers\CHtml;
use Helpers\CArrayHelper;
?>
<div class="table-footer">
    <?
        if(count($groupOperations)){
            ?>
                <div class="table-actions <?=$listId;?>_group_actions">
                    <label>С выбранными:</label>
                    <div class="group_action_choice">
                        <?
                            $groupOptions = ["" => "Выберите действие"] + CArrayHelper::map($groupOperations, "value", "title");
                            
                            echo CHtml::select($groupOperationKey, $groupOptions, "", [
                                "id"        => $listId . "_group_choice_select",
                                "class"     => "form-control",
                                "disabled"  => "disabled",
                                "onchange"  => $onChangeGroupOperation ? $onChangeGroupOperation . "(this)" : "$(\"#" . $listId . "_group_choice_apply\").get(0).disabled = (this.value.length ? false : true);"
                            ]);
                        ?>
                    </div>
                    <?=($groupOperationsContent ? $groupOperationsContent : "");?>
                    <button class="btn btn-info btn-icon" disabled="disabled" id="<?=$listId?>_group_choice_apply" onclick="<?=CHtml::escape("$.entityDataList(\"" . $listId . "\").applyGroupOperation();")?>"><i class="icon-checkmark"></i></button>
                </div>
            <?
        }
    ?>
    <div class="<?=$listId;?>_pagination" id="<?=$listId;?>_pagination_footer">
        <?
            CWidget::render("pagination", "index", "index", [
                "pagination"    => $pagination,
                "urlPageKey"    => $pageKey,
                "urlPath"       => $baseURL
            ]);
        ?>
    </div>
</div>