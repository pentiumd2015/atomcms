<?
namespace Entity\Field\Renderer;

use Helpers\CHtml;
use Helpers\CBuffer;

class DateTimeRenderer extends StringRenderer{
    public function renderFilter($value, array $data = [], array $options = []){
        $field = $this->getField();
        
        CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?=$field->title;?>:</label>
                    <div class="col-sm-9">
                        <?=CHtml::text($options["requestName"] . "[" . $field->getName() . "]", $value, array(
                            "class" => "form-control input-sm"
                        ));?>
                        <span class="help-block">пример: 15 ; 1-69 ; 1;56 ; -54 ; 126-</span>
                    </div>
                </div>
            <?
        return CBuffer::end();
    }
    
    public function renderDetail($value, array $data = [], array $options = []){
        $field      = $this->getField();
        $fieldName  = $field->getName();
        
        if($field->disabled){
            if(!$value){//если поле отключено и нет значения, то ничего отображать не надо
                return "";
            }
            
            CBuffer::start();
            ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?=$field->title . ":" . ($field->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                    <div class="col-sm-6 control-content">
                        <div class="control-disabled"><b><?=(is_array($value) ? reset($value) : $value);?></b></div>
                    </div>
                </div>
            <?
            return CBuffer::end();
        }
        
        CBuffer::start();
            $containerID = uniqid($fieldName . "_");
            
            if($field->multi){
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$field->title . ":" . ($field->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                            <div class="col-sm-6 control-content">
                                <div class="entity_field_container_<?=$containerID;?>">
                                    <?
                                        $values = is_array($value) ? $value : [$value];
                                    
                                        $i = 0;
                                        
                                        foreach($values AS $value){
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <?
                                                            echo CHtml::text($options["requestName"] . "[" . $fieldName . "][]", $value, [
                                                                "class" => "form-control " . $containerID
                                                            ]);
                                                            
                                                            if($i == 0 && $field->description){
                                                                ?>
                                                                    <span class="help-block"><?=$field->description;?></span>
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
                                    <?=CHtml::text($options["requestName"] . "[" . $fieldName . "][]", "", [
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
            /*    $value = is_array($value) ? reset($value) : $value ;
                ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?=$field->title . ":" . ($field->required ? "<span class=\"mandatory\">*</span>" : "");?></label>
                        <div class="col-sm-6 control-content">
                            <div class="row">
                                <div class="col-sm-9">
                                    <?
                                        echo CHtml::text($params["requestName"] . "[" . $fieldName . "]", $value, [
                                            "class" => "form-control " . $containerID
                                        ]);
                                    
                                        if($field->description){
                                            ?>
                                                <span class="help-block"><?=$field->description;?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                        if($value){
                            $dateTime = new CDateTime($value);
                        }else{
                            $dateTime = new CDateTime;
                        }
                    ?>
                    <script type="text/javascript">
                        $(function(){
                            
                            $('.<?=$containerID;?>').datepicker({
                                showOtherMonths: true,
                                dateFormat: "dd.mm.yy",
                                defaultDate: '<?=$dateTime->format("d.m.Y")?>'
                            });
                        })
                    </script>
                <?*/
            }
        
        return CBuffer::end();
    }
    
    public function renderParams(){
        
    }
}