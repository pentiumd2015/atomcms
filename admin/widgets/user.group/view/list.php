<div class="page-header">
    <div class="page-title">
        <h3>Группы пользователей <small>Список групп</small></h3>
    </div>
</div>

<?
CWidget::render("admin.filter", "index", "index", array(
    "filterID"      => $filterID,
    "data"          => $_REQUEST["f"],
    "fields"        => $arDisplayFilterFields,
    "settingsURL"   => "/admin/ajax/?widget=entity.display&method=getFilterSettings&entity=" . CUserGroup::getClass()
));

$displayListSettings = CJSON::encode(array(
    "url"       => "/admin/ajax/?widget=entity.display&method=getListSettings&entity=" . CUserGroup::getClass(),
    "width"     => 700,
    "height"    => 400
));
?>
<div class="row">
    <div class="col-md-8">
        <a class="btn btn-info" href="<?=$addURL;?>">
            <i class="icon-plus"></i>
            Добавить группу
        </a>
    </div>
    <div class="col-md-4 text-right">
        <a class="btn btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="javascript:(new CModal(<?=CHtml::chars($displayListSettings);?>)).show();" title="Настройка отображения">
            <i class="icon-cogs"></i>
        </a>
    </div>
</div>
<?
CWidget::render("admin.list", "index", "index", array(
    "listID"        => $listID,
    "data"          => $arUserGroups,
    "pagination"    => $obPagination,
    "fields"        => $arDisplayListFields,
    "extraData"     => array(
        "url" => $editURL
    ),
    "controls" => function($arRow, $primaryKey, $arParams){
        $arControls = array(
            CHtml::a("<i class=\"icon-pencil\"></i> Редактировать", str_replace("{ID}", $arRow[$primaryKey], $arParams["extraData"]["url"]))
        );

        if(isset($arRow["alias"]) && !CUserGroup::isSystemGroup($arRow["alias"])){
            $arControls[] = CHtml::a("<i class=\"icon-remove\"></i> Удалить", "javascript: modalDelete(" . $arRow[$primaryKey] . ");");
        }
        
        return $arControls;
    }
));
?>
<script type="text/javascript">
function onApplyFilterAfter(data){
    $.adminList("<?=$listID;?>").refresh(data);
}

function onApplyListAfter(){
    $('[data-toggle="tooltip"]').tooltip();
    
    $.filterList("<?=$filterID;?>").hideSpinner();
}

var currentModal;

function modalDelete(id){
    var buttons = AdminTools.html.button("Удалить", {
        "class"     : "btn btn-danger",
        "onclick"   : "applyDelete(" + id + ")"
    });
    
    buttons+= AdminTools.html.button("Отмена", {
        "class"     : "btn btn-primary",
        "data-mode" : "close"
    });
    
    currentModal = new CModal({
        "title"     : "<i class=\"icon-remove\"></i> Подтверждение удаления",
        "body"      : "<p>Вы действительно хотите удалить группу?</p>",
        "buttons"   : buttons,
        "width"     : 340,
        "height"    : 70
    }).show();
}

function applyDelete(id){
    if(id){
        $.ajax({
            type    : "POST",
            url     : "/admin/ajax/",
            data    : {
                widget  : "<?=$this->name;?>",
                method  : "remove",
                id      : id
            },
            dataType: "json",
            success : function(r){
                if(r && r.result == 1){
                    if(!r.hasErrors){
                        $.note({
                            header  : "Успешно!", 
                            title   : "Группа успешно удалена", 
                            theme   : "success",
                            duration: 5000
                        });
                        
                        currentModal.close();
                        
                        $.adminList("<?=$listID;?>").refresh();
                    }else{
                        $.note({
                            header  : "Ошибка удаления!", 
                            title: "Группа не была удалена", 
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
            title: "Группа не была удалена", 
            theme: "error",
            duration: 5000
        });
    }
}
</script>