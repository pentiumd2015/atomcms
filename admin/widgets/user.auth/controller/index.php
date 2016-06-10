<?
    $obUser     = $this->app("user");

    $redirectURL = $_GET["redirectUrl"] ? $_GET["redirectUrl"] : BASE_URL ;
    
    if($obUser->isAuth()){
        CHttpResponse::redirect($redirectURL);
    }

    if($_REQUEST["auth"]){
        $arData = $_REQUEST["auth"];
        
        if($obUser->auth($arData["login"], $arData["password"], $arData["remember"])){
            CHttpResponse::redirect($redirectURL);
        }
    }
    
    $this->includeView();
?>

<?php 

?>

<?php 

?>