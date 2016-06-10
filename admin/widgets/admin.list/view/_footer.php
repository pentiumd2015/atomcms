<div class="table-footer">
    <?
        if(count($arParams["groupOperations"])){
            ?>
                <div class="table-actions <?=$arParams["listID"];?>_group_actions">
                    <label>С выбранными:</label>
                    <div class="group_action_choice">
                        <?
                            $arGroupOptions = CArrayHelper::getKeyValue($arParams["groupOperations"], "value", "title");
                            $arGroupOptions = ["" => "Выберите действие"] + $arGroupOptions;
                            
                            echo CHtml::select($arParams["groupKey"], $arGroupOptions, "", [
                                "id"        => $arParams["listID"] . "_group_choice_select",
                                "class"     => "form-control",
                                "disabled"  => "disabled",
                                "onchange"  => $arParams["onChangeGroupOperation"] ? $arParams["onChangeGroupOperation"] . "(this)" : "$(\"#" . $arParams["listID"] . "_group_choice_apply\").get(0).disabled = (this.value.length ? false : true);"
                            ]);
                        ?>
                    </div>
                    <?=($arParams["groupOperationsContent"] ? $arParams["groupOperationsContent"] : "");?>
                    <button class="btn btn-info btn-icon" disabled="disabled" id="<?=$arParams["listID"]?>_group_choice_apply" onclick="$.adminList(&quot;<?=$arParams["listID"];?>&quot;).applyGroupOperation();"><i class="icon-checkmark"></i></button>
                </div>
            <?
        }
    
        if($arParams["pagination"]){
            ?>
                <div class="<?=$arParams["listID"];?>_pagination" id="<?=$arParams["listID"];?>_pagination_footer">
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