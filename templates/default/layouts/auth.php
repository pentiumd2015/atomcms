<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" href="/favicon.ico" />
        <title>Atom Key - Авторизация</title>
        <link href="<?=$this->template->templatePath;?>/css/bootstrap.css" rel="stylesheet" />
        <link href="<?=$this->template->templatePath;?>/css/bootstrap-theme.css" rel="stylesheet" />
        <link href="<?=$this->template->templatePath;?>/css/style.css" rel="stylesheet" />
        <script src="<?=$this->template->templatePath;?>/js/jquery.min.js"></script>
        <script src="<?=$this->template->templatePath;?>/js/bootstrap.js"></script>
        <script src="<?=$this->template->templatePath;?>/js/script.js"></script>
        <?Page\Page::showHead();?>
    </head>
    <body>
         <style>
            body{
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #eee;
            }
        </style>
        <div class="container">
            <?=$this->content;?>
        </div>
    </body>
</html>