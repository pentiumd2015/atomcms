<table<?=CHtml::getAttributeString($arParams["tableAttributes"]);?>>
    <?
        if($arParams["showHead"] && count($arParams["fields"])){
            ?>
                <thead<?=CHtml::getAttributeString($arParams["headAttributes"]);?>>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <div class="checkbox checkbox-primary">
                                <?=CHtml::checkbox($arParams["listID"] . "_checkbox_all", false, [
                                    "id"        => $arParams["listID"] . "_checkbox_all",
                                    "onchange"  => "$.adminList(&quot;" . $arParams["listID"] . "&quot;).selectAll(this.checked);"
                                ]);?>
                                <label for="<?=$arParams["listID"];?>_checkbox_all"></label>
                            </div>
                        </th>
                        <th class="text-center" style="width: 60px;">&nbsp;</th>
                        <?
                            $sortField  = $_REQUEST[$arParams["sortKey"]];
                            $by         = strtolower($_REQUEST[$arParams["sortByKey"]]);
                            
                            foreach($arParams["fields"] AS $obField){
                                $fieldName      = $obField->getName();
                                $sortClass      = "sorting";
                                
                                $isChecked = false;
                                
                                if($sortField == $fieldName && ($by == "asc" || $by == "desc")){
                                    $sortClass.= " sorting_" . $by;
                                    
                                    $isChecked = true;
                                }
                                
                                $arAttributes = [
                                    "class"     => $sortClass,
                                    "onclick"   => "$.adminList(&quot;" . $arParams["listID"] . "&quot;).applySort(this);"
                                ];
                                
                                ?>
                                    <th<?=CHtml::getAttributeString($arAttributes);?>>
                                        <?=$obField->title;?>
                                        <div class="sortable_field">
                                            <?
                                                echo CHtml::radio($arParams["sortKey"], $isChecked, [
                                                    "style" => "display:none;",
                                                    "value" => $fieldName
                                                ]);
                                                
                                                echo CHtml::radio($arParams["sortByKey"], ($isChecked && $by == "asc"), [
                                                    "style" => "display:none;",
                                                    "value" => "asc"
                                                ]);
                                                
                                                echo CHtml::radio($arParams["sortByKey"], ($isChecked && $by == "desc"), [
                                                    "style" => "display:none;",
                                                    "value" => "desc"
                                                ]);
                                            ?>
                                        </div>
                                    </th>
                                <?
                            }
                        ?>
                    </tr>
                </thead>
            <?
		}
    ?>
    <tbody<?=CHtml::getAttributeString($arParams["bodyAttributes"]);?>>
        <?
            if(count($arParams["fields"]) && count($arParams["listData"])){
                $arCachedFieldRenderer = [];

                foreach($arParams["fields"] AS $obField){
                    $arCachedFieldRenderer[$obField->getName()] = $obField->getRenderer();
                }
                
                foreach($arParams["listData"] AS $arRow){
                    $id = $arRow[$arParams["options"]["primaryKey"]];
                    ?>
                        <tr data-id="<?=$id;?>">
                            <td class="text-center">
                                <div class="checkbox checkbox-primary">
                                    <?=CHtml::checkbox("checkbox_item[]", false, array(
                                        "id"        => "checkbox_item_" . $id,
                                        "class"     => $arParams["listID"] . "_checkbox_item",
                                        "onchange"  => "$.adminList(&quot;" . $arParams["listID"] . "&quot;).selectItem(this);",
                                        "value"     => $id,
                                    ));?>
                                    <label for="checkbox_item_<?=$id;?>"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-info btn-icon btn-sm" data-toggle="dropdown">
                                        <i class="icon-menu2"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?
                                            $controls = $arParams["controls"];
                        
                                            foreach($arParams["controls"]($arRow, $arParams["options"]) AS $control){
                                                ?>
                                                    <li><?=$control;?></li>
                                                <?
                                            }
                                        ?>
                                    </ul>
                                </div>
                            </td>
                            <?
                                $rowCallback = $arParams["onRowOptions"];
                                $arRowOptions = $rowCallback ? $rowCallback($arRow, $arParams["options"]) : $arParams["options"];
                                
                                foreach($arParams["fields"] AS $obField){
                                    $fieldName = $obField->getName();
                                    
                                    $cellCallback   = $arParams["onCellOptions"];
                                    $arCellOptions  = $cellCallback ? $cellCallback($arRow[$fieldName], $arRow, $arRowOptions, $obField) : $arRowOptions ;
                                    ?>
                                        <td><?=$arCachedFieldRenderer[$fieldName]->renderList($arRow[$fieldName], $arRow, $arCellOptions);?></td>
                                    <?
                                }
                            ?>
                        </tr>
                    <?
        		}
            }else{
                ?>
                    <tr>
                        <td class="text-center" colspan="<?=count($arParams["fields"]) + 2;?>">Нет данных</td>
                    </tr>
                <?
            }
        ?>
    </tbody>
</table>