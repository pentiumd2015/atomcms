<?
namespace Entity\Field\Renderer;

use \Helpers\CHtml;
use \Helpers\CBuffer;

class StringRenderer extends BaseRenderer{
    public function renderList($value, array $arListData = [], array $arOptions = []){
        if($this->getField()->multi){
            $arValues = is_array($value) ? $value : [$value] ;
            
            $str = "";
            
            foreach($arValues AS $value){
                $str.= "<span class=\"label label-primary\">" . $value . "</span> ";
            }
        }else{
            $str = is_array($value) ? reset($value) : $value ;
        }
        
        if(!strlen($str)){
            $str = "-";
        }
        
        return $arOptions["linkable"] ? CHtml::a($str, $arOptions["url"]) : $str ;
    }
    
    public function renderFilter($value, array $arData = [], array $arOptions = []){
        $obField        = $this->getField();
        $arParams       = $this->getParams();
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$obField->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($arParams["requestName"] . "[" . $obField->getName() . "]", $value, [
                            "class" => "form-control input-sm"
                        ]);?>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, array $arData = [], array $arOptions = []){
        $obField        = $this->getField();
        $fieldName      = $obField->getName();
        $arParams       = $this->getParams();
        
        CBuffer::start();
            if($obField->disabled){
                if(!$value){//если поле отключено и нет значения, то ничего отображать не надо
                    return "";
                }
                ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?=$obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="control-disabled"><b><?=(is_array($value) ? reset($value) : $value);?></b></div>
                        </div>
                    </div>
                <?
                return CBuffer::end();
            }
            
            if($obField->multi){
                $containerID = uniqid($fieldName . "_");
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                            <div class="col-sm-6 control-content">
                                <div class="entity_field_container_<?=$containerID;?>">
                                    <?
                                        $arValues = is_array($value) ? $value : [$value];
                                    
                                        $i = 0;
                                        
                                        foreach($arValues AS $value){
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <?
                                                            echo CHtml::text($arParams["requestName"] . "[" . $fieldName . "][]", $value, [
                                                                "class" => "form-control"
                                                            ]);
                                                            
                                                            if($i == 0 && $obField->description){
                                                                ?>
                                                                    <span class="help-block"><?=$obField->description;?></span>
                                                                <?
                                                            }
                                                        ?>
                                                    </div>
                                                    <?
                                                        if($i > 0){
                                                            ?>
                                                                <div class="col-sm-3">
                                                                    <?=CHtml::button("<i class=\"icon icon-close\"></i>", [
                                                                        "class"     => "btn btn-icon btn-primary btn-xs",
                                                                        "onclick"   => "$(this).closest(\".row\").remove();"
                                                                    ]);?>
                                                                </div>
                                                            <?
                                                        }
                                                    ?>
                                                </div>
                                            <?
                                            
                                            $i++;
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <?=CHtml::button("<i class=\"icon icon-plus\"></i> Добавить", [
                                    "class"     => "btn btn-primary",
                                    "onclick"   => "addValue" . $containerID . "(this);"
                                ]);?>
                            </div>
                        </div>
                        <script type="template/html" id="entity_field_value_template_<?=$containerID;?>">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?=CHtml::text($arParams["requestName"] . "[" . $fieldName . "][]", "", [
                                        "class" => "form-control",
                                    ]);?>
                                </div>
                                <div class="col-sm-3">
                                    <?=CHtml::button("<i class=\"icon icon-close\"></i>", [
                                        "class"     => "btn btn-icon btn-primary btn-xs",
                                        "onclick"   => "$(this).closest(\".row\").remove();"
                                    ]);?>
                                </div>
                            </div>
                        </script>
                        <script type="text/javascript">
                            function addValue<?=$containerID;?>(el){
                                var newItem = AdminTools.getTemplate("#entity_field_value_template_<?=$containerID;?>", {});
                                $(".entity_field_container_<?=$containerID;?>").append(newItem);
                            }
                        </script>
                        <style>
                        .entity_field_container_<?=$containerID;?> .row + .row{
                            margin-top: 15px;
                        }
                        </style>
                    <?
            }else{
                $value = is_array($value) ? reset($value) : $value ;
                ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?=$obField->title . ":" . ($obField->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?
                                        echo CHtml::text($arParams["requestName"] . "[" . $fieldName . "]", $value, [
                                            "class" => "form-control"
                                        ]);
                                    
                                        if($obField->description){
                                            ?>
                                                <span class="help-block"><?=$obField->description;?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?
            }
        
        return CBuffer::end();
    }
    
    public function renderParams(){
        
    }
}