<?
use Helpers\CUrl;
use Helpers\CJson;
use Helpers\CHtml;


/*$p = CModuleAccessGroup::add([
    "title"     => "Группы пользователей",
    "module_id" => 1
]);*/

/*$p = CModuleAccess::add([
    "module_access_group_id" => 1,
    "title" => "Удаление",
    "code"  => "delete"
]);*/

/*CModuleAccess::getAccessByModule("");

$p = CModuleAccess::query()->fetchAll();
*/

//$p = CAtom::$app->user->can("user.view");
//p($p);
?>
<div class="page-header">
    <div class="page-title">
        <h3>Пользователи <small>Список пользователей</small></h3>
    </div>
</div>
<?
CWidget::render("entity.data.filter", "index", "index", [
    "filterId"      => $filterId,
    "requestName"   => "f",
    "entity"        => CUser::getClass(),
    "filterData"    => $filterData ,
    "fields"        => $displayFilterFields,
    "settingsUrl"   => CUrl::to("/admin/ajax", [
        "widget"    => "entity.display",
        "method"    => "getFilterSettings",
        "entity"    => CUser::getClass()
    ])
]);

$displayListSettings = CJson::encode([
    "url"       => CUrl::to("/admin/ajax", [
        "widget"    => "entity.display",
        "method"    => "getListSettings",
        "entity"    => CUser::getClass()
    ]),
    "width"     => 700,
    "height"    => 400
]);

CWidget::render("entity.data.list", "index", "index", [
    "listId"        => $listId,
    "dataSource"    => $dataSource,
    "fields"        => $displayListFields,
    "headPanel"     => [
        [
            "attributes"    => ["class" => "col-md-8"],
            "items"         => [
                CHtml::a("<i class=\"icon-plus\"></i> Добавить пользователя", $addUrl, [
                    "class" => "btn btn-info"
                ])
            ]
        ],
        [
            "attributes"    => ["class" => "col-md-4 text-right"],
            "items"         => [
                CHtml::a("<i class=\"icon-cogs\"></i>", "#", [
                    "class"             => "btn btn-primary btn-icon",
                    "data-placement"    => "top",
                    "data-toggle"       => "tooltip",
                    "onclick"           => "(new CModal(" . $displayListSettings . ")).show();return false;",
                    "title"             => "Настройка отображения"
                ])
            ]
        ]
    ],
    "options"   => [
        "url" => $editUrl
    ],
    "onRowOptions" => function($row, $options){
        $options["url"] = str_replace("{ID}", $row[$options["primaryKey"]], $options["url"]);

        return $options;
    },
    "onCellOptions" => function($value, $row, $options, $fieldName){
        $options["linkable"] = $fieldName == "login";

        return $options;
    },
    "controls" => function($row, $options){
        $pk = $options["primaryKey"];
        
        $controls = [
            CHtml::a("<i class=\"icon-remove\"></i> Активировать", "javascript: modalActiva(" . $row[$pk] . ");"),
            CHtml::a("<i class=\"icon-remove\"></i> Деактивировать", "javascript: modalDelete(" . $row[$pk] . ");"),
            CHtml::a("<i class=\"icon-pencil\"></i> Редактировать", str_replace("{ID}", $row[$pk], $options["url"]))
        ];
        
        if($row[$pk] > 1){
            $controls[] = CHtml::a("<i class=\"icon-remove\"></i> Удалить", "javascript: modalDelete(" . $row[$pk] . ");");
        }
        
        return $controls;
    },
    "groupOperations" => [
        [
            "title" => "Активировать",
            "value" => "activate"
        ],
        [
            "title" => "Деактивировать",
            "value" => "deactivate"
        ],
        [
            "title" => "Удалить",
            "value" => "delete"
        ]
    ]
]);
?>
<script type="text/javascript">
function onApplyFilterAfter(data){
    $.entityDataList("<?=$listId;?>").refresh(data);
}

function onApplyListAfter(){
    $('[data-toggle="tooltip"]').tooltip();
    
    $.entityDataFilter("<?=$filterId;?>").hideSpinner();
}

function modalDelete(id){
    var buttons = AdminTools.html.button("Удалить", {
        "class"     : "btn btn-danger",
        "onclick"   : "applyDelete(" + id + ")"
    });
    
    buttons+= AdminTools.html.button("Отмена", {
        "class"     : "btn btn-primary",
        "data-mode" : "close"
    });
    
    var modal = new CModal({
        "title"     : "<i class=\"icon-remove\"></i> Подтверждение удаления",
        "body"      : "<p>Вы действительно хотите удалить пользователя?</p>",
        "buttons"   : buttons,
        "width"     : 340,
        "height"    : 70
    }).show();

    $(document).data("modal.current", modal);
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

                        var modal = $(document).data("modal.current");

                        if(modal){
                            modal.close();
                        }
                        
                        $.entityDataList("<?=$listId;?>").refresh();
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