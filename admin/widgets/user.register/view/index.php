<?
use \Helpers\CHtml;
?>
<style>
.form-signin {
    max-width: 330px;
    padding: 15px;
    margin: 0 auto;
}
</style>
<form class="col-sm-offset-4 form-horizontal col-sm-4" method="POST">
    <h2 class="form-signin-heading">Регистрация</h2>
    <?
        if(count($arErrors)){
            ?>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="alert alert-danger" role="alert">
                            <strong>Ошибка!</strong>
                            <br />
                            <?
                                foreach($arErrors AS $errorField => $arFieldErrors){
                                    echo implode("<br/>", $arFieldErrors);
                                }
                            ?>
                        </div>
                    </div>
                </div>
            <?
        }
    ?>
    <div class="form-group">
        <div class="col-sm-12">
            <?=CHtml::text("register[email]", $arFormData["email"], array(
                "class"         => "form-control",
                "placeholder"   => "Введите Ваш E-mail"
            ));?>                        
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-12">
            <?=CHtml::password("register[password]", $arFormData["password"], array(
                "class"         => "form-control",
                "placeholder"   => "Придумайте пароль"
            ));?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-12">
            <?=CHtml::submit("Зарегистрироваться", array(
                "class" => "btn-block btn btn-primary"
            ));?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-12 text-right">
            Если Вы уже зарегистрированы, <a href="/auth/">Авторизуйтесь</a>.
        </div>
    </div>
</form>

<?php 

?>

<?php 

?>