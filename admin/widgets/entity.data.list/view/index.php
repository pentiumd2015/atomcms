<?
use Helpers\CHtml;
?>
<div id="<?=$listId;?>_wrapper" class="entity_data_list_wrapper">
    <?
        if(count($headPanel)){
            ?>
                <div class="row">
                    <?
                        foreach($headPanel AS $panelItem){
                            ?>
                                <div<?=(isset($panelItem["attributes"]) && is_array($panelItem["attributes"]) ? CHtml::getAttributeString($panelItem["attributes"]) : "")?>>
                                    <?
                                        if(isset($panelItem["items"]) && is_array($panelItem["items"])){
                                            foreach($panelItem["items"] AS $item){
                                                echo $item;
                                            }
                                        }
                                    ?>
                                </div>
                            <?
                        }
                    ?>
                </div>
                <br/> 
            <?
        }
    ?>
    <div class="panel panel-default">
        <?include(__DIR__ . "/_head.php");?>
        <div class="admin_list_spinner"><i class="icon-spinner3 spin"></i></div>
        <?include(__DIR__ . "/_table.php");?>
        <?include(__DIR__ . "/_footer.php");?>
        <script type="text/javascript">
            (function(){
                if(typeof $.entityDataList == "undefined"){
                    $.entityDataList = function(listId, obj){
                        if(listId){
                            if(typeof obj == "undefined"){
                                return $(document).data("entity-data-list-" + listId);
                            }else{
                                $(document).data("entity-data-list-" + listId, obj)
                                return obj;
                            }
                        }
                    }
                }
                
                var params = <?=$jsonParams;?>;
                
                $.entityDataList(params.listId, new EntityDataList(params));
            })($);
        </script>
    </div>
</div>