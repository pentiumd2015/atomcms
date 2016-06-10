<?
    $obUser = $this->app("user");
    
    if($_REQUEST["logout"] || !$obUser->isAuth()){
        \Models\User::logout();
        
        \Helpers\CHttpResponse::redirect("/auth/");
        $this->app()->end();
    }
    
    $this->setData(array(
        "obUser" => $obUser
    ));
    
    $this->includeView();
?>

<?php 

?>

<?php 

?>