<?
use \Models\User;
   
$arFormData = array();

$arErrors = array();

if($_REQUEST["register"]){
    $arFormData = $_REQUEST["register"];
    
    if(empty($arFormData["email"])){
        $arErrors["email"][] = "Заполните E-mail";
    }else{
        if(!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $arFormData["email"])){
            $arErrors["email"][] = "Введите корректный E-mail";
        }else{
            list(, $domain) = explode("@", $arFormData["email"], 2);
            if(!checkdnsrr($domain, "MX")){
                $arErrors["email"][] = "Введенный E-mail не существует";
            }
        }
    }
    
    if(empty($arFormData["password"])){
        $arErrors["password"][] = "Заполните пароль";
    }else{
        if(strlen($arFormData["password"]) < 5 || strlen($arFormData["password"]) > 20){
            $arErrors["password"][] = "Пароль должен быть от 5 до 20 символов";
        }
    }
    
    if(!count($arErrors)){
        $obExist = \Models\User::find("login=?", array($arFormData["email"]));
        
        if($obExist){
            $arErrors["email"][] = "Пользователь с таким E-mail уже существует";
        }
    }
    
    if(!count($arErrors)){
        $userID = \Models\User::add(array(
            "login"     => $arFormData["email"], 
            "password"  => $arFormData["password"]
        ));
        
        if($userID){
            $obUser = new \Models\User;
            
            if($obUser->auth($arFormData["email"], $arFormData["password"], true)){
                $this->app()->setUser($obUser);
            }
        }
    }
}

if($this->app()->getUser()->isAuth()){
    \Helpers\CHttpResponse::redirect("/");
}

$this->setData(array(
    "arFormData"    => $arFormData,
    "arErrors"      => $arErrors
));

$this->includeView();
?>

<?php 

?>

<?php 

?>