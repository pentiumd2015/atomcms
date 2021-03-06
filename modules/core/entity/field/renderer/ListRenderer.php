<?
namespace Entity\Field\Renderer;

use Helpers\CHtml;
use Helpers\CArrayHelper;
use Helpers\CBuffer;

class ListRenderer extends BaseRenderer{
    public $values = NULL;
    
    public function loadValues(){
        /*load at once*/
        
        if($this->values == NULL){
            $field = $this->getField();

            if(is_callable($field->values)){
                $callback = $field->values;
                $values = $callback($this);
            }else if(is_array($field->values)){
                $values = $field->values;
            }
            
            $this->values = is_array($values) ? $values : [] ;
            
            if(!is_array(reset($this->values))){
                foreach($this->values AS $value => $title){
                    $this->values[$value] = ["title" => $title];
                }
            }
        }
        /*load at once*/
        
        return $this->values;
    }
    
    public function renderList($value, array $arData = [], array $options = []){
        $field        = $this->getField();
        $values       = $this->loadValues();

        if($field->multi){
            if(is_array($value) && count($value)){
                $str = "";

                foreach($value AS $valueID){
                    if(isset($values[$valueID])){
                        $class = isset($values[$valueID]["class"]) ? $values[$valueID]["class"] : "label-primary";
                        $str.= "<span class=\"label " . $class . "\">" . $values[$valueID]["title"] . "</span> ";
                    }
                }
            }else{
                $str = "-";
            }
        }else{
            if(is_array($value)){
                $value = reset($value);
            }
            
            if(isset($values[$value])){
                $class = isset($values[$value]["class"]) ? $values[$value]["class"] : "label-primary";
                $str = "<span class=\"label " . $class . "\">" . $values[$value]["title"] . "</span> ";
            }else{
                $str = "-";
            }
        }
        
        return $str;
    }
    
    public function renderFilter($value, array $arData = [], array $options = []){
        $field        = $this->getField();
        $values       = $this->loadValues();
        
        $optionsList = ["" => "Не выбрана"];
        $optionsList  = CArrayHelper::replace($optionsList, CArrayHelper::map($values, null, "title"));

        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$field->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::select($options["requestName"] . "[" . $field->getName() . "]", $optionsList, $value, [
                            "class" => "form-control"
                        ]);?>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, array $arData = [], array $options = []){
        $field    = $this->getField();
        $values   = $this->loadValues();
        $fieldName  = $field->getName();

        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?=$field->title;?>:<?=($field->required ? "<span class=\"mandatory\">*</span>" : "")?></label>
                    <div class="col-sm-6 control-content">
                        <?
                            echo CHtml::hidden($options["requestName"] . "[" . $fieldName . "]");
                            
                            if($field->multi){
                                if(!is_array($value)){
                                    $value = [];
                                }
                                
                                foreach($values AS $valueID => $arValue){
                                    ?>
                                        <div class="checkbox checkbox-primary">
                                            <?=CHtml::checkbox($options["requestName"] . "[" . $fieldName . "][]", (in_array($valueID, $value)), [
                                                "value" => $valueID,
                                                "id"    => $fieldName . "_" . $valueID
                                            ]);?>
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
                                $value = is_array($value) ? reset($value) : $value ;
                                
                                foreach($values AS $valueID => $arValue){
                                    ?>
                                        <div class="radio radio-primary">
                                            <?=CHtml::radio($options["requestName"] . "[" . $fieldName . "]", ($valueID == $value), [
                                                "value" => $valueID,
                                                "id"    => $fieldName . "_" . $valueID
                                            ]);?>
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
    
    public function renderParams(){
        $field        = $this->getField();
        $containerID    = uniqid("f" . $field->getName());
        $values       = $field->loadValues();
        
        if($field->multi){
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
        
        /*$fieldVariants = EntityFieldVariant::findAll(array(
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
                                        <?=CHtml::radio("entity_field[params][view]", ($currentView == $view), [
                                            "id"    => "view_" . $view,
                                            "value" => $view
                                        ]);?>
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
                                
                                if(count($fieldVariants)){
                                    $i = 0;
                                    foreach($fieldVariants AS $fieldVariant){
                                        ?>
                                            <li class="item_field_variant">
                                                <div class="row">
                                                    <div class="col-sm-1 drag_handle">
                                                        <i class="icon-move"></i>
                                                    </div>
                                                    <div class="col-sm-1 variant_item_id"><?=$fieldVariant->entity_field_variant_id;?></div>
                                                    <div class="col-sm-8">
                                                        <?=CHtml::text("entity_field[variants][" . $variantCounter . "][title]", $fieldVariant->title, [
                                                            "class" => "form-control",
                                                        ]);?>
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
                                                <div class="col-sm-1 variant_item_id"><?=$fieldVariant->entity_field_variant_id;?></div>
                                                <div class="col-sm-8">
                                                    <?=CHtml::text("entity_field[variants][" . $variantCounter . "][title]", "", [
                                                        "class" => "form-control",
                                                    ]);?>
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
                                <?=CHtml::text("entity_field[variants][#index#][title]", "", [
                                    "class" => "form-control",
                                ]);?>
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