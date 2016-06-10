<?
$arInfo = $obEntity->getInfo();
?>
<div class="page-header">
    <div class="page-title">
        <h3><?=$arInfo["title"];?><small>Настройка сущности</small></h3>
    </div>
</div>
<div class="tabbable">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#view_tab_1" data-toggle="tab"><i class="icon-list"></i> Дополнительные поля</a>
        </li>
        <li>
            <a href="#view_tab_2" data-toggle="tab"><i class="icon-user"></i> Доступ</a>
        </li>
    </ul> 
    <form class="form-horizontal" method="POST" action="<?=$editURL;?>">
        <div class="tab-content with-padding">
            <div class="tab-pane fade in active" id="view_tab_1">
                <?include(__DIR__ . "/include/fields.php")?>
            </div>
            <div class="tab-pane fade" id="view_tab_2">
                23232
                <div class="form-actions text-right">
                    <button type="submit" class="btn btn-primary">Применить</button>
                </div>
            </div>
            
        </div>
    </form>
</div>

<?php 

?>

<?php 

?>