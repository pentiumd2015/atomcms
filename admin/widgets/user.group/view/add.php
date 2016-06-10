<div class="page-header">
    <div class="page-title">
        <h3>Новая группа</h3>
    </div>
</div>
<?
    CWidget::render("admin.detail", "index", "index", array(
        "formID"        => $formID,
        "data"          => $arUserGroup,
        "tabs"          => $arDisplayDetailFields,
        "settingsURL"   => "/admin/ajax/?widget=entity.display&method=getDetailSettings&entity=" . CUserGroup::getClass()
    ));
?>