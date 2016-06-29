<?
use Helpers\CJson;
use Helpers\CHtml;

if(count($tabs)){
    ?>
        <div class="tabbable" id="<?=$formId;?>_wrapper">
            <ul class="nav nav-tabs pull-left">
                <?
                    $index = 0;
                    
                    foreach($tabs AS $tabName => $tab){
                        ?>
                            <li<?=($index == 0 ? ' class="active"' : "")?>>
                                <a href="#<?=$tabName;?>" data-toggle="tab"><?=$tab["title"];?></a>
                            </li>
                        <?
                        $index++;
                    }
                ?>
            </ul>
            <?
                if(isset($settingsUrl)){
                    $displaySettings = CJson::encode([
                        "url"       => $settingsUrl,
                        "width"     => 800,
                        "height"    => 400
                    ]);
                    ?>
                        <a class="pull-right btn btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="#" onclick="(new CModal(<?=CHtml::escape($displaySettings);?>)).show();return false;" title="Настройка отображения">
                            <i class="icon-cogs"></i>
                        </a>
                    <?
                }
            ?>
            <div class="clearfix"></div>
            <form<?=CHtml::getAttributeString($attributes);?>>
                <div class="tab-content with-padding">
                    <?
                        $index = 0;
                        
                        foreach($tabs AS $tabName => $tab){
                            ?>
                                <div class="tab-pane fade<?=($index == 0 ? ' in active' : "")?>" id="<?=$tabName;?>">
                                    <?
                                        foreach($tab["fields"] AS $fieldName => $field){
                                            $value = isset($formData[$fieldName]) ? $formData[$fieldName] : null ;

                                            echo call_user_func_array($field["renderer"], [$value, $formData, $rendererParams]);
                                        }
                                    ?>
                                </div>
                            <?
                            $index++;
                        }
                    ?>
                    <div class="form-actions text-right">
                        <?=is_array($buttons) ? implode("", $buttons) : $buttons ;?>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            (function(){
                if(typeof $.entityDataForm == "undefined"){
                    $.entityDataForm = function(formId, obj){
                        if(formId){
                            if(typeof obj == "undefined"){
                                return $(document).data("entity-data-form-" + formId);
                            }else{
                                $(document).data("entity-data-form-" + formId, obj)
                                return obj;
                            }
                        }
                    }
                }

                var params = <?=$jsonParams;?>;
                
                $.entityDataForm(params.formId, new EntityDataForm(params));
            })($);
        </script>
    <?
}