<div class="page-header">
    <div class="page-title">
        <h3><?=($mode == "add" ? "Новый пользователь" : $formData["login"] . "<small>Редактирование пользователя</small>")?></h3>
    </div>
</div>
<?
    CWidget::render("entity.data.detail", "index", "index", [
        "formId"        => $formId,
        "formData"      => $formData,
        "entity"        => CUser::getClass(),
        "tabs"          => $displayFields,
        "settingsUrl"   => "/admin/ajax/?widget=entity.display&method=getDetailSettings&entity=" . CUser::getClass()
    ]);
?>