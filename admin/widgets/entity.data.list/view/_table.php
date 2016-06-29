<?
use Helpers\CHtml;
?>
<table<?=CHtml::getAttributeString($tableAttributes);?>>
    <?
        if(count($fields)){
            ?>
                <thead<?=CHtml::getAttributeString($headAttributes);?>>
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <div class="checkbox checkbox-primary">
                                <?=CHtml::checkbox($listId . "_checkbox_all", false, [
                                    "id"        => $listId . "_checkbox_all",
                                    "onchange"  => CHtml::escape("$.entityDataList(\"" . $listId . "\").selectAll(this.checked);")
                                ]);?>
                                <label for="<?=$listId;?>_checkbox_all"></label>
                            </div>
                        </th>
                        <th class="text-center" style="width: 60px;">&nbsp;</th>
                        <?
                            $orders = $query->getOrders();
                            
                            foreach($fields AS $fieldName => $field){
                                if($field->sortable){
                                    $by = isset($orders[$fieldName]) ? strtolower($orders[$fieldName]) : null ;
                                    ?>
                                        <th class="sorting<?=($by ? " sorting_" . $by : "")?>" onclick="<?=CHtml::escape("$.entityDataList(\"" . $listId . "\").applySort(this);");?>">
                                            <?=$field->title;?>
                                            <div class="sortable_field">
                                                <?
                                                    echo CHtml::radio($sortKey, ($by ? true : false), [
                                                        "style" => "display:none;",
                                                        "value" => $fieldName
                                                    ]);
                                                    
                                                    echo CHtml::radio($sortByKey, ($by == "asc"), [
                                                        "style" => "display:none;",
                                                        "value" => "asc"
                                                    ]);
                                                    
                                                    echo CHtml::radio($sortByKey, ($by == "desc"), [
                                                        "style" => "display:none;",
                                                        "value" => "desc"
                                                    ]);
                                                ?>
                                            </div>
                                        </th>
                                    <?
                                }else{
                                    ?>
                                        <th><?=$field->title;?></th>
                                    <?
                                }
                            }
                        ?>
                    </tr>
                </thead>
                <tbody<?=CHtml::getAttributeString($bodyAttributes);?>>
                    <?
                        if(count($data)){
                            foreach($data AS $row){
                                $id = $row[$primaryKey];
                                $options["primaryKey"] = $primaryKey;
                                ?>
                                    <tr data-id="<?=$id;?>">
                                        <td class="text-center">
                                            <div class="checkbox checkbox-primary">
                                                <?=CHtml::checkbox("checkbox_item[]", false, [
                                                    "id"        => "checkbox_item_" . $id,
                                                    "class"     => $listId . "_checkbox_item",
                                                    "onchange"  => "$.entityDataList(&quot;" . $listId . "&quot;).selectItem(this);",
                                                    "value"     => $id,
                                                ]);?>
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
                                                        if($controls){
                                                            foreach($controls($row, $options) AS $control){
                                                                ?>
                                                                    <li><?=$control;?></li>
                                                                <?
                                                            }
                                                        }
                                                    ?>
                                                </ul>
                                            </div>
                                        </td>
                                        <?
                                            $rowOptions = $onRowOptions ? $onRowOptions($row, $options) : $options;
                                            
                                            foreach($fields AS $fieldName => $field){
                                                $cellOptions  = $onCellOptions ? $onCellOptions($row[$fieldName], $row, $rowOptions, $fieldName) : $rowOptions ;
                                                ?>
                                                    <td><?=call_user_func_array($field["renderer"], [$row[$fieldName], $row, $cellOptions, $fieldName]);?></td>
                                                <?
                                            }
                                        ?>
                                    </tr>
                                <?
                    		}
                        }else{
                            ?>
                                <tr>
                                    <td class="text-center" colspan="<?=count($fields) + 2;?>">Нет данных</td>
                                </tr>
                            <?
                        }
                    ?>
                </tbody>
            <?
		}
    ?>
</table>