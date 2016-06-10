<div class="login-wrapper">
    <form method="POST">
        <div class="popup-header">
            <span class="text-semibold">Авторизация</span>
        </div>
        <div class="well">
            <div class="form-group has-feedback">
                <label>Логин</label>
                <?=CHtml::text("auth[login]", $_REQUEST["auth"]["login"], array(
                    "class"         => "form-control",
                    "placeholder"   => "Введите логин"
                ));?>
                <i class="icon-users form-control-feedback"></i>
            </div>
            <div class="form-group has-feedback">
                <label>Пароль</label>
                <?=CHtml::password("auth[password]", $_REQUEST["auth"]["password"], array(
                    "class"         => "form-control",
                    "placeholder"   => "Введите пароль"
                ));?>
                <i class="icon-lock form-control-feedback"></i>
            </div>
            <div class="row form-actions">
                <div class="col-xs-7">
                    <div class="checkbox checkbox-primary">
                        <?=CHtml::checkbox("auth[remember]", ($_REQUEST["auth"]["remember"] ? 1 : 0), array(
                            "value" => 1,
                            "id"    => "remember_me"
                        ));?>
                        <label for="remember_me">Запомнить меня</label>
                    </div>
                </div>
                <div class="col-xs-5">
                    <?=CHtml::button("Вход", array(
                        "type"  => "submit",
                        "class" => "btn btn-primary pull-right"
                    ));?>
                </div>
            </div>
        </div>
    </form>
</div>

<?php 

?>

<?php 

?>