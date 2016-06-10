<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Londinium - premium responsive admin template by Eugene Kopyov</title>
        <link href="<?=$this->template->templatePath;?>css/bootstrap.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->template->templatePath;?>css/londinium-theme.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->template->templatePath;?>css/styles.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->template->templatePath;?>css/icons.min.css" rel="stylesheet" type="text/css">
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&amp;subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
        
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        
        <link href="<?=$this->template->templatePath;?>css/bootstrap.checkbox.css" rel="stylesheet" type="text/css" />
        
        <?CPage::showHead();?>
    </head>
    <body>
        <?CWidget::render("user.auth", "index", "index");?>
    </body>
</html>