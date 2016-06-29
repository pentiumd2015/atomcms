<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Test</title>
        <link href="<?=$this->baseUrl;?>css/bootstrap.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->baseUrl;?>css/londinium-theme.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->baseUrl;?>css/styles.css" rel="stylesheet" type="text/css">
        <link href="<?=$this->baseUrl;?>css/icons.min.css" rel="stylesheet" type="text/css">
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&amp;subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
        
        
        <link href="<?=$this->baseUrl;?>css/note.jquery.css" rel="stylesheet" type="text/css">
        
        
        
        
        
        
        
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.js"></script>
        
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/admin.js"></script>
        
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/modal.js"></script>
        
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/plugins/forms/inputnum.jquery.js"></script>
        <link href="<?=$this->baseUrl;?>css/bootstrap.checkbox.css" rel="stylesheet" type="text/css" />

        <script type="text/javascript" src="<?=$this->baseUrl;?>js/plugins/forms/select.jquery.js"></script>
        <link href="<?=$this->baseUrl;?>css/select.jquery.css" rel="stylesheet" type="text/css" />
        
        
        
        
        
        
        
        
        <!--<script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/charts/sparkline.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/uniform.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/select2.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/inputmask.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/autosize.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/inputlimit.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/listbox.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/multiselect.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/validate.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/tags.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/switch.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/uploader/plupload.full.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/uploader/plupload.queue.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/wysihtml5/wysihtml5.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/forms/wysihtml5/toolbar.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/daterangepicker.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/fancybox.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/moment.js"></script>
        -->
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/plugins/interface/note.jquery.js"></script>
        <!--<script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/datatables.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/colorpicker.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/fullcalendar.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/timepicker.min.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>/js/plugins/interface/collapsible.min.js"></script>-->
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/bootstrap.js"></script>
        <script type="text/javascript" src="<?=$this->baseUrl;?>js/application.js"></script>
        <?$this->showHead();?>
    </head>
<body class="sidebar-wide">
    <?CWidget::render("navigation", "index", "index");?>
    <div class="page-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-content">
                <!-- User dropdown -->
                <?/*<div class="user-menu dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?=$this->app("template")->templatePath;?>/images/demo/users/face3.png" alt="">
                        <div class="user-info">Madison Gartner <span>Web Developer</span></div>
                    </a>
                    <div class="popup dropdown-menu dropdown-menu-right">
                        <div class="thumbnail">
                            <div class="thumb">
                                <img alt="" src="<?=$this->app("template")->templatePath;?>/images/demo/users/face3.png">
                                <div class="thumb-options">
                                    <span>
                                        <a href="#" class="btn btn-icon btn-success"><i class="icon-pencil"></i></a>
                                        <a href="#" class="btn btn-icon btn-success"><i class="icon-remove"></i></a>
                                    </span>
                                </div>
                            </div>
                            <div class="caption text-center">
                                <h6>Madison Gartner <small>Front end developer</small></h6>
                            </div>
                        </div>
                        <ul class="list-group">
                            <li class="list-group-item"><i class="icon-pencil3 text-muted"></i> My posts <span class="label label-success">289</span></li>
                            <li class="list-group-item"><i class="icon-people text-muted"></i> Users online <span class="label label-danger">892</span></li>
                            <li class="list-group-item"><i class="icon-stats2 text-muted"></i> Reports <span class="label label-primary">92</span></li>
                            <li class="list-group-item"><i class="icon-stack text-muted"></i> Balance
                                <h5 class="pull-right text-danger">$45.389</h5>
                            </li>
                        </ul>
                    </div>
                </div>*/?>
                <!-- /user dropdown -->
                <?CWidget::render("menu", "sidebar", "sidebar");?>
            </div>
        </div>
        <!-- /sidebar -->
        <div class="page-content-wrapper">
            <div class="page-content">
                <?CWidget::render("breadcrumbs", "index", "index");?>
                <?=$content;?>
            </div>
        </div>
    </div>
    <div class="footer clearfix">
        <div class="pull-left">© 2015. Test</div>
    </div>
</body>
</html>