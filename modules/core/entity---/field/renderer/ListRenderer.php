<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CArrayHelper;
use \CBuffer;

class ListRenderer extends Renderer{
    public function renderList($value, $arRow, $arParams = array()){
        $obField        = $this->getField();
        $arValues       = $obField->loadValues();
        $arFieldParams  = $obField->getParams();
        
        if($arFieldParams["multi"] || is_array($value)){
            if(count($value)){
                $str = "";
                                        
                foreach($value AS $valueID){
                    if(isset($arValues[$valueID])){
                        $str.= "<span class=\"label label-primary\">" . $arValues[$valueID]["title"] . "</span> ";
                    }
                }
            }else{
                $str = "-";
            }
        }else{
            if(isset($arValues[$value])){
                $str = "<span class=\"label label-primary\">" . $arValues[$value]["title"] . "</span> ";
            }else{
                $str = "-";
            }
        }
        
        return $str;
    }
    
    public function renderFilter($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arValues       = $obField->loadValues();
        $arFieldParams  = $obField->getParams();
        
        $arOptionsList  = CArrayHelper::getKeyValue($arValues, "id", "title");
        $arOptionsList  = CArrayHelper::replace(array("" => "Не выбрана"), $arOptionsList);
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$arFieldParams["title"];?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::select($arParams["requestArrayName"] . "[" . $obField->getFieldName() . "]", $arOptionsList, $value, array(
                            "class" => "form-control"
                        ));?>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arValues       = $obField->loadValues();
        $arFieldParams  = $obField->getParams();
        $fieldName      = $obField->getFieldName();

        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?=$arFieldParams["title"];?>:<?=($arFieldParams["required"] ? "<span class=\"mandatory\">*</span>" : "")?></label>
                    <div class="col-sm-6 control-content">
                        <?
                            echo CHtml::hidden($arParams["requestArrayName"] . "[" . $fieldName . "]");
                            
                            if($arFieldParams["multi"]){
                                if(!is_array($value)){
                                    $value = array();
                                }
                                
                                foreach($arValues AS $valueID => $arValue){
                                    ?>
                                        <div class="checkbox checkbox-primary">
                                            <?=CHtml::checkbox($arParams["requestArrayName"] . "[" . $fieldName . "][]", (in_array($valueID, $value)), array(
                                                "value" => $valueID,
                                                "id"    => $fieldName . "_" . $valueID
                                            ));?>
                                            <label for="<?=$fieldName . "_" . $valueID;?>"><?=$arValue["title"];?></label>
                                        </div>
                                        <?
                                        if($arValue["description"]){
                                            ?>
                                                <span class="help-block"><?=$arValue["description"];?></span>
                                            <?
                                        }
                                }
                            }else{
                                foreach($arValues AS $valueID => $arValue){
                                    ?>
                                        <div class="radio radio-primary">
                                            <?=CHtml::radio($arParams["requestArrayName"] . "[" . $fieldName . "]", ($valueID == $value), array(
                                                "value" => $valueID,
                                                "id"    => $fieldName . "_" . $valueID
                                            ));?>
                                            <label for="<?=$fieldName . "_" . $valueID;?>"><?=$arValue["title"];?></label>
                                        </div>
                                        <?
                                        if($arValue["description"]){
                                            ?>
                                                <span class="help-block"><?=$arValue["description"];?></span>
                                            <?
                                        }
                                }
                            }
                        ?>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderParams($arParams = array()){
        $obField        = $this->getField();
        $arFieldParams  = $obField->getParams();
        $containerID    = uniqid("f" . $obField->getFieldName());
        $arValues       = $obField->loadValues();
        
        if($arFieldParams["multi"]){
            $arViews = array(
                "multiselect"   => "Выпадающий список",
                "checkbox"      => "Флажки",
            );
        }else{
            $arViews = array(
                "select"        => "Выпадающий список",
                "radio"         => "Переключатели",
            );
        }
        
        if(isset($this->obEntityField->params["view"])){
            $currentView = $this->obEntityField->params["view"];
        }else{
            $currentView = key($arViews);
        }
        
        /*$arFieldVariants = EntityFieldVariant::findAll(array(
            "condition" => "entity_field_id=?",
            "order"     => "priority ASC"
        ), array($this->obEntityField->entity_field_id));*/
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Вид: </label>
                    <div class="col-sm-6">
                        <?
                            foreach($arViews AS $view => $viewTitle){
                                ?>
                                    <div class="radio radio-primary">
                                        <?=CHtml::radio("entity_field[params][view]", ($currentView == $view), array(
                                            "id"    => "view_" . $view,
                                            "value" => $view
                                        ));?>
                                        <label for="view_<?=$view;?>"><?=$viewTitle;?></label>
                                    </div>
                                <?
                            }
                        ?>
                    </div>
                </div>
                <legend>Список значений:</legend>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="row" style="padding: 5px 10px;">
                            <div class="col-sm-1">&nbsp;</div>
                            <div class="col-sm-1 variant_head">ID</div>
                            <div class="col-sm-8 variant_head">Значение</div>
                        </div>
                        <ul class="item_field_variants_container_<?=$containerID;?>">
                            <?
                                $variantCounter = 0;
                                
                                if(count($arFieldVariants)){
                                    $i = 0;
                                    foreach($arFieldVariants AS $obFieldVariant){
                                        ?>
                                            <li class="item_field_variant">
                                                <div class="row">
                                                    <div class="col-sm-1 drag_handle">
                                                        <i class="icon-move"></i>
                                                    </div>
                                                    <div class="col-sm-1 variant_item_id"><?=$obFieldVariant->entity_field_variant_id;?></div>
                                                    <div class="col-sm-8">
                                                        <?=CHtml::text("entity_field[variants][" . $variantCounter . "][title]", $obFieldVariant->title, array(
                                                            "class" => "form-control",
                                                        ));?>
                                                    </div>
                                                    <?
                                                        if($i > 0){
                                                            ?>
                                                                <div class="col-sm-2 text-right">
                                                                    <a href="#" class="btn btn-icon btn-primary btn-xs item_field_variant_remove"><i class="icon icon-close"></i></a>
                                                                </div>
                                                            <?
                                                        }
                                                    ?>
                                                </div>
                                            </li>
                                        <?                                        
                                        $i++;
                                    }
                                }else{
                                    ?>
                                        <li class="item_field_variant">
                                            <div class="row">
                                                <div class="col-sm-1 drag_handle">
                                                    <i class="icon-move"></i>
                                                </div>
                                                <div class="col-sm-1 variant_item_id"><?=$obFieldVariant->entity_field_variant_id;?></div>
                                                <div class="col-sm-8">
                                                    <?=CHtml::text("entity_field[variants][" . $variantCounter . "][title]", "", array(
                                                        "class" => "form-control",
                                                    ));?>
                                                </div>
                                            </div>
                                        </li>
                                    <?
                                    $variantCounter++;
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-9 text-right">
                                <button class="btn btn-primary add_new_item_field_variant_<?=$containerID;?>" type="button">
                                    <i class="icon icon-plus"></i> Добавить
                                </button>
                            </div>
                            <div class="col-sm-3"></div>
                        </div>
                    </div>
                </div>
                <script type="template/html" class="item_field_variant_template_<?=$containerID;?>">
                    <li class="item_field_variant">
                        <div class="row">
                            <div class="col-sm-1 drag_handle">
                                <i class="icon-move"></i>
                            </div>
                            <div class="col-sm-1 variant_item_id">&nbsp;</div>
                            <div class="col-sm-8">
                                <?=CHtml::text("entity_field[variants][#index#][title]", "", array(
                                    "class" => "form-control",
                                ));?>
                            </div>
                            <div class="col-sm-2 text-right">
                                <a href="#" class="btn btn-icon btn-primary btn-xs item_field_variant_remove"><i class="icon icon-close"></i></a>
                            </div>
                        </div>
                    </li>
                </script>
                <script type="text/javascript">
                $(function(){
                    var variantCounter = <?=$variantCounter;?>;
                    
                    $(document).on("click", ".add_new_item_field_variant_<?=$containerID;?>", function(){
                        var newItem = AdminTools.getTemplate(".item_field_variant_template_<?=$containerID;?>", {
                            index: variantCounter
                        });
                        
                        variantCounter++;
                        
                        $(".item_field_variants_container_<?=$containerID;?>").append(newItem);
                    });
                                                    
                    $(document).on("click", ".item_field_variants_container_<?=$containerID;?> .item_field_variant_remove", function(e){
                        e.preventDefault();
                        $(this).closest('.item_field_variant').remove();
                    });
                    
                    $(".item_field_variants_container_<?=$containerID;?>").sortable({
                        handle      : ".drag_handle",
                        placeholder : "variant_placeholder"
                    }).disableSelection();
                });
                </script>
                <style>
                .item_field_variants_container_<?=$containerID;?>{
                    list-style:none;
                    padding-left: 0;
                }
                
                .item_field_variants_container_<?=$containerID;?> li{
                    border: 1px #DBDBDB solid;
                    padding: 5px 10px;
                    font-size: 0px;
                    background:#fff;
                }
                
                .item_field_variants_container_<?=$containerID;?> li input[type="text"]{
                    background-color: #FAFAFA;
                    height: 30px;
                }
                
                .item_field_variants_container_<?=$containerID;?> li.ui-sortable-helper{
                    background-color: #5bc0de;
                    border-color: #46b8da;
                    color: #fff;
                    cursor: move;
                }
                
                .item_field_variants_container_<?=$containerID;?> li + li{
                    margin-top: 3px;
                }
                
                .variant_item_id{
                    margin: 5px 0;
                    font-size: 14px;
                }
                
                .variant_head{
                    margin: 5px 0;
                    font-weight:bold;
                }
                
                .item_field_variants_container_<?=$containerID;?> .drag_handle{
                    margin: 5px 0;
                    cursor:move;
                }
                
                .item_field_variants_container_<?=$containerID;?> li.variant_placeholder {
                    background: #F1F1F1;
                }
                </style>
            <?
        
        return CBuffer::end();
    }
}
?>