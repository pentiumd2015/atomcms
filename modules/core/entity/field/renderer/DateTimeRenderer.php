<?
namespace Entity\Field\Renderer;

use \CHtml;
use \CBuffer;
use \CDateTime;

class DateTimeRenderer extends StringRenderer{
    public function renderFilter($value, array $arData = [], array $arOptions = []){
        $obField = $this->getField();
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$obField->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($arParams["requestArrayName"] . "[" . $obField->getName() . "]", $value, array(
                            "class" => "form-control input-sm"
                        ));?>
                        <span class="help-block">пример: 15 ; 1-69 ; 1;56 ; -54 ; 126-</span>
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
            
            $containerID = uniqid($fieldName . "_");
            
            if($obField->multi){
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
                                                                "class" => "form-control " . $containerID
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
                                        "class" => "form-control " . $containerID
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
                            $(function(){
                                
                                
                                $('.<?=$containerID;?>').datepicker({
                                    showOtherMonths: true,
                                    dateFormat: "dd.mm.yy",
                                    defaultDate: <??>
                                });
                            })
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
                                            "class" => "form-control " . $containerID
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
                        if($value){
                            $obDateTime = new CDateTime($value);
                        }else{
                            $obDateTime = new CDateTime;
                        }
                    ?>
                    <script type="text/javascript">
                        $(function(){
                            
                            $('.<?=$containerID;?>').datepicker({
                                showOtherMonths: true,
                                dateFormat: "dd.mm.yy",
                                defaultDate: '<?=$obDateTime->format("d.m.Y")?>'
                            });
                        })
                    </script>
                <?
            }
        
        return CBuffer::end();
    }
    
    public function renderParams(){
        
    }
}
?>