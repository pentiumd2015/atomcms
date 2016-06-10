<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CBuffer;

class StringRenderer extends Renderer{
    public function renderList($value, $arRow, $arParams = array()){
        $arFieldParams = $this->getField()->getParams();

        if($arFieldParams["multi"] && count($value)){
            $arValues = $value;
            
            $value = "";
            
            foreach($arValues AS $title){
                $value.= "<span class=\"label label-primary\">" . $title . "</span> ";
            }
        }else{
            $value = is_array($value) ? reset($value) : $value ;
        }
        
        if(!$value){
            $value = "-";
        }
        
        return $value;
    }
    
    public function renderFilter($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arFieldParams  = $obField->getParams();
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$arFieldParams["title"];?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($arParams["requestArrayName"] . "[" . $obField->getFieldName() . "]", $value, array(
                            "class" => "form-control input-sm"
                        ));?>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, $arData, $arParams = array()){
        $obField        = $this->getField();
        $arFieldParams  = $obField->getParams();
        $fieldName      = $obField->getFieldName();

        if($arFieldParams["disabled"]){
            $str.= "<b style=\"line-height: 24px;\">" . $value . "</b>";;
        }else{
            CBuffer::start();
            
            if($arFieldParams["multi"]){
                $containerID = uniqid($fieldName . "_");
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$arFieldParams["title"] . ":" . ($arFieldParams["required"] ? "<span class=\"mandatory\">*</span>" : "");?></label>
                            <div class="col-sm-6 control-content">
                                <div class="entity_field_container_<?=$containerID;?>">
                                    <?
                                        if(is_array($value)){
                                            $i = 0;
                                            
                                            $arValues = $value;
                                            
                                            foreach($arValues AS $title){
                                                ?>
                                                    <div class="row">
                                                        <div class="col-sm-9">
                                                            <?=CHtml::text($arParams["requestArrayName"] . "[" . $fieldName . "][]", $title, array(
                                                                "class" => "form-control"
                                                            ));?>
                                                            <?
                                                                if($i == 0 && $arParams["description"]){
                                                                    ?>
                                                                        <span class="help-block"><?=$arParams["description"];?></span>
                                                                    <?
                                                                }
                                                            ?>
                                                        </div>
                                                        <?
                                                            if($i > 0){
                                                                ?>
                                                                    <div class="col-sm-3">
                                                                        <?=CHtml::button("<i class=\"icon icon-close\"></i>", array(
                                                                            "class"     => "btn btn-icon btn-primary btn-xs",
                                                                            "onclick"   => "$(this).closest(\".row\").remove();"
                                                                        ));?>
                                                                    </div>
                                                                <?
                                                            }
                                                        ?>
                                                    </div>
                                                <?
                                                
                                                $i++;
                                            }
                                        }else{
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <?=CHtml::text($arParams["requestArrayName"] . "[" . $fieldName . "][]", "", array(
                                                            "class" => "form-control",
                                                        ));?>
                                                        <?
                                                            if($arParams["description"]){
                                                                ?>
                                                                    <span class="help-block"><?=$arParams["description"];?></span>
                                                                <?
                                                            }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-2">&nbsp;</div>
                            <div class="col-sm-10">
                                <?=CHtml::button("<i class=\"icon icon-plus\"></i> Добавить", array(
                                    "class"     => "btn btn-primary",
                                    "onclick"   => "addValue" . $containerID . "(this);"
                                ));?>
                            </div>
                        </div>
                        <script type="template/html" id="entity_field_value_template_<?=$containerID;?>">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?=CHtml::text($arParams["requestArrayName"] . "[" . $fieldName . "][]", "", array(
                                        "class" => "form-control",
                                    ));?>
                                </div>
                                <div class="col-sm-3">
                                    <?=CHtml::button("<i class=\"icon icon-close\"></i>", array(
                                        "class"     => "btn btn-icon btn-primary btn-xs",
                                        "onclick"   => "$(this).closest(\".row\").remove();"
                                    ));?>
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
                        <label class="col-sm-2 control-label"><?=$arFieldParams["title"] . ":" . ($arFieldParams["required"] ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?=CHtml::text($arParams["requestArrayName"] . "[" . $fieldName . "]", $value, array(
                                        "class" => "form-control"
                                    ));?>
                                    <?
                                        if($arParams["description"]){
                                            ?>
                                                <span class="help-block"><?=$arParams["description"];?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?
            }
            
            $str = CBuffer::end();
        }
        
        return $str;
    }
}
?>