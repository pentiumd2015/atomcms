<div class="page-header">
    <div class="page-title">
        <h3><?=($mode == "add" ? "Новая группа" : $formData["title"] . "<small>Редактирование группы</small>")?></h3>
    </div>
</div>
<?
    CWidget::render("entity.data.detail", "index", "index", [
        "formId"        => $formId,
        "formData"      => $formData,
        "entity"        => CUserGroup::getClass(),
        "tabs"          => $displayFields,
        "settingsUrl"   => "/admin/ajax/?widget=entity.display&method=getDetailSettings&entity=" . CUserGroup::getClass()
    ]);
?>