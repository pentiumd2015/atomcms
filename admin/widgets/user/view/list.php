<div class="page-header">
    <div class="page-title">
        <h3>Пользователи <small>Список пользователей</small></h3>
    </div>
</div>
<?
CWidget::render("admin.filter", "index", "index", [
    "filterID"      => $filterID,
    "requestName"   => "f",
    "filterData"    => $_REQUEST["f"],
    "fields"        => $arDisplayFilterFields,
    "settingsURL"   => "/admin/ajax/?widget=entity.display&method=getFilterSettings&entity=" . CUser::getClass()
]);

$displayListSettings = CJSON::encode([
    "url"       => "/admin/ajax/?widget=entity.display&method=getListSettings&entity=" . CUser::getClass(),
    "width"     => 700,
    "height"    => 400
]);
?>
<div class="row">
    <div class="col-md-8">
        <a class="btn btn-info" href="<?=$addURL;?>">
            <i class="icon-plus"></i>
            Добавить пользователя
        </a>
    </div>
    <div class="col-md-4 text-right">
        <a class="btn btn-primary btn-icon" data-placement="top" data-toggle="tooltip" href="#" onclick="(new CModal(<?=CHtml::chars($displayListSettings);?>)).show();return false;" title="Настройка отображения">
            <i class="icon-cogs"></i>
        </a>
    </div>
</div>
<?
CWidget::render("admin.list", "index", "index", [
    "listID"        => $listID,
    "listData"      => $arUsers,
    "pagination"    => $obPagination,
    "fields"        => $arDisplayListFields,
    "options"  => [
        "url" => $editURL
    ],
    "onRowOptions" => function($arRow, $arOptions){
        $arOptions["url"] = str_replace("{ID}", $arRow[$arOptions["primaryKey"]], $arOptions["url"]);

        return $arOptions;
    },
    "onCellOptions" => function($value, $arRow, $arOptions, $obField){
        $arOptions["linkable"] = $obField->getName() == "login";
        
        return $arOptions;
    },
    "controls" => function($arRow, $arOptions){
        $pk = $arOptions["primaryKey"];
        
        $arControls = [
            CHtml::a("<i class=\"icon-pencil\"></i> Редактировать", str_replace("{ID}", $arRow[$pk], $arOptions["url"]))
        ];
        
        if($arRow[$pk] > 1){
            $arControls[] = CHtml::a("<i class=\"icon-remove\"></i> Удалить", "javascript: modalDelete(" . $arRow[$pk] . ");");
        }
        
        return $arControls;
    }
]);
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
        "body"      : "<p>Вы действительно хотите удалить пользователя?</p>",
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
                            title   : "Пользователь успешно удален", 
                            theme   : "success",
                            duration: 5000
                        });
                        
                        currentModal.close();
                        
                        $.adminList("<?=$listID;?>").refresh();
                    }else{
                        $.note({
                            header  : "Ошибка удаления!", 
                            title: "Пользователь не был удален", 
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
            title: "Пользователь не был удален", 
            theme: "error",
            duration: 5000
        });
    }
}
</script>