<div class="page-header">
    <div class="page-title">
        <h3><?=$arFormData["login"];?><small>Редактирование пользователя</small></h3>
    </div>
</div>
<?  
    CWidget::render("admin.detail", "index", "index", [
        "formID"        => $formID,
        "formData"      => $arFormData,
        "tabs"          => $arDisplayFields,
        "settingsURL"   => "/admin/ajax/?widget=entity.display&method=getDetailSettings&entity=" . CUser::getClass()
    ]);
?>