<?
use Helpers\CHtml;

$this->view->addJs(BASE_URL . $this->path . "js/filter.js");
$this->view->addCss(BASE_URL . $this->path . "css/filter.css");
?>
<div class="row filter_settings_container">
    <div class="col-sm-6">
        <h5>Список доступных полей:</h5>
        <ul class="available_fields_list fields_list connected_sortable">
            <?
                foreach(array_keys($display->getFieldNames()) AS $fieldName){
                    if(in_array($fieldName, $displayFields) || !isset($fields[$fieldName])){
                        continue;
                    }
                    
                    $field = $fields[$fieldName];
                    ?>
                        <li>
                            <div class="field_drag_handle"><i class="icon-move"></i></div>
                            <?=$field->title;?>
                            <?=CHtml::hidden("data[][field]", $fieldName);?>
                        </li>
                    <?
                }
            ?>
        </ul>
    </div>
    <div class="col-sm-6">
        <form class="filter_settings_form" method="POST" action="<?=BASE_URL . "ajax/";?>">
            <?=CHtml::hidden("widget", $this->name);?>
            <?=CHtml::hidden("method", "setDisplaySettings");?>
            <?=CHtml::hidden("entity", $entity->getClass());?>
            <?=CHtml::hidden("type", "filter");?>
            <h5>Список выбранных полей:</h5>
            <ul class="chosen_fields_list fields_list connected_sortable">
                <?
                    foreach($displayFields AS $fieldName){
                        if(!isset($fields[$fieldName])){
                            continue;
                        }
                        
                        $field = $fields[$fieldName];
                        ?>
                            <li>
                                <div class="field_drag_handle"><i class="icon-move"></i></div>
                                <?=$field->title;?>
                                <?=CHtml::hidden("data[][field]", $fieldName);?>
                            </li>
                        <?
                    }
                ?>
            </ul>
        </form>
    </div>
</div>