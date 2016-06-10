<?
use \Entity\ExtraField;

$fieldPk = ExtraField::getPk();

$addFieldParams = CJSON::encode(array(
    "url"       => "/admin/ajax/?widget=" . $this->name . "&method=addField&entity=" . $obEntity::getClass(),
    "width"     => 1000,
    "height"    => 500
));
?>
<div class="row">
    <div class="col-sm-12">
        <p>
            <a href="javascript:(new CModal(<?=CHtml::chars($addFieldParams);?>)).show();" class="btn btn-info">Добавить поле</a>
        </p>
    </div>
</div>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th style="width: 60px;">ID</th>
            <th style="width: 60px;">&nbsp;</th>
            <th>Название поля</th>
            <th>Тип</th>
            <th>Описание</th>
            <th class="text-center" style="width: 120px;">Обязатальное</th>
            <th class="text-center" style="width: 120px;">Уникальное</th>
        </tr>
    </thead>
    <tbody id="entity_field_list">
        <?
            foreach($arExtraFields AS $arExtraField){
                $obFieldType = $arExtraField["type"];
                
                if($obFieldType){
                    $arInfo = $obFieldType->getInfo();
                }
                
                $editFieldParams = CJSON::encode(array(
                    "url"       => "/admin/ajax/?widget=" . $this->name . "&method=editField&fieldID=" . $arExtraField[$fieldPk] . "&entity=" . $obEntity::getClass(),
                    "width"     => 1000,
                    "height"    => 500
                ));
                ?>
                    <tr data-id="<?=$arExtraField[$fieldPk];?>">
                        <td><?=$arExtraField[$fieldPk];?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-info btn-icon btn-sm" data-toggle="dropdown">
                                    <i class="icon-menu2"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="javascript:(new CModal(<?=CHtml::chars($editFieldParams);?>)).show();"><i class="icon-pencil"></i> Редактировать</a>
                                    </li>
                                    <li>
                                        <a href="#" onclick="modalDelete(<?=$arExtraField[$fieldPk];?>);"><i class="icon-remove"></i> Удалить</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <a href="javascript:(new CModal(<?=CHtml::chars($editFieldParams);?>)).show();"><?=$arExtraField["title"];?></a>
                        </td>
                        <td><?=($arInfo["title"] ? $arInfo["title"] : "н/д");?></td>
                        <td><?=$arExtraField["description"];?></td>
                        <td class="text-center"><?=($arExtraField["required"] ? "Да" : "Нет");?></td>
                        <td class="text-center"><?=($arExtraField["is_unique"] ? "Да" : "Нет");?></td>
                    </tr>
                <?
            }
        ?>
    </tbody>
</table>
<script>
var currentModal;

function modalDelete(fieldID){
    var buttons = AdminTools.html.button("Удалить", {
        "class"     : "btn btn-danger",
        "onclick"   : "applyDelete(" + fieldID + ")"
    });
    
    buttons+= AdminTools.html.button("Отмена", {
        "class"     : "btn btn-primary",
        "data-mode" : "close"
    });
    
    currentModal = new CModal({
        "title"     : "<i class=\"icon-remove\"></i> Подтверждение удаления",
        "body"      : "<p>Вы действительно хотите удалить поле?</p>",
        "buttons"   : buttons,
        "width"     : 340,
        "height"    : 70
    }).show();
}

function applyDelete(fieldID){
    if(fieldID){
        $.ajax({
            type    : "POST",
            url     : "/admin/ajax/",
            data    : {
                widget  : "<?=$this->name;?>",
                method  : "deleteField",
                fieldID : fieldID
            },
            dataType: "json",
            success : function(r){
                if(r && r.result == 1){
                    if(!r.hasErrors){
                        $.note({
                            header  : "Успешно!", 
                            title   : "Поле успешно удалено", 
                            theme   : "success",
                            duration: 5000
                        });
                        
                        currentModal.close();
                        
                        AdminTools.ajaxRefresh(["#entity_field_list"]);
                    }else{
                        $.note({
                            header  : "Ошибка удаления!", 
                            title: "Поле не было удалено", 
                            theme: "error",
                            duration: 5000
                        });
                    }
                }
            }
        });
    }else{
        $.note({
            header  : "Ошибка удаления!", 
            title: "Поле не было удалено", 
            theme: "error",
            duration: 5000
        });
    }
}
</script>