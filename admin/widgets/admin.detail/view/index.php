<?
$formJsPath = $this->path . "js/adminForm.js";

if(CHttpRequest::isAjax()){
    ?>
        <script type="text/javascript" src="<?=$formJsPath;?>"></script>
    <?
}else{
    CPage::addJS($formJsPath);
}

if(count($arParams["tabs"])){
    ?>
        <div class="tabbable" id="<?=$arParams["formID"];?>_wrapper">
            <ul class="nav nav-tabs pull-left">
                <?
                    $index = 0;
                    
                    foreach($arParams["tabs"] AS $tabName => $arTab){
                        ?>
                            <li<?=($index == 0 ? ' class="active"' : "")?>>
                                <a href="#<?=$tabName;?>" data-toggle="tab"><?=$arTab["title"];?></a>
                            </li>
                        <?
                        $index++;
                    }
                ?>
            </ul>
            <?
                if(isset($arParams["settingsURL"])){
                    $displaySettings = CJSON::encode(array(
                        "url"       => $arParams["settingsURL"],
                        "width"     => 800,
                        "height"    => 400
                    ));
                    ?>
                        <a class="pull-right btn btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="#" onclick="(new CModal(<?=CHtml::chars($displaySettings);?>)).show();return false;" title="Настройка отображения">
                            <i class="icon-cogs"></i>
                        </a>
                    <?
                }
            ?>
            <div class="clearfix"></div>
            <form<?=CHtml::getAttributeString($arParams["attributes"]);?>>
                <div class="tab-content with-padding">
                    <?
                        $index = 0;
                        
                        foreach($arParams["tabs"] AS $tabName => $arTab){
                            ?>
                                <div class="tab-pane fade<?=($index == 0 ? ' in active' : "")?>" id="<?=$tabName;?>">
                                    <?
                                        foreach($arTab["fields"] AS $obField){
                                            $fieldName  = $obField->getName();
                                            $value      = isset($arParams["formData"][$fieldName]) ? $arParams["formData"][$fieldName] : null ;
                                            
                                            echo $obField->getRenderer()
                                                         ->setParams($arRendererParams)
                                                         ->renderDetail($value, $arParams["formData"]);
                                        }
                                    ?>
                                </div>
                            <?
                            $index++;
                        }
                    ?>
                    <div class="form-actions text-right">
                        <?=is_array($arParams["buttons"]) ? implode("", $arParams["buttons"]) : $arParams["buttons"] ;?>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">
            $(function(){
                if(typeof $.adminForm == "undefined"){
                    $.adminForm = function(formID, obj){
                        if(formID){
                            if(typeof obj == "undefined"){
                                return $(document).data("admin-form-" + formID);
                            }else{
                                $(document).data("admin-form-" + formID, obj)
                                return obj;
                            }
                        }
                    }
                }
                
                var arParams = <?=CJSON::encode($arParams);?>;
                
                $.adminForm(arParams.formID, new AdminForm(arParams));
            });
        </script>
    <?
}