<?
$jsDir = $this->path;

if(app("request")->isAjax()){
    ?>
        <script type="text/javascript" src="<?=$jsDir;?>js/colResizable-1.5.min.js"></script>
        <script type="text/javascript" src="<?=$jsDir;?>js/adminList.js"></script>
    <?
}else{
    CPage::addJS($jsDir . "js/colResizable-1.5.min.js");
    CPage::addJS($jsDir . "js/adminList.js");
}
?>
<div id="<?=$arParams["listID"];?>_wrapper">
    <div class="panel panel-default">
        <?$this->includeView("_head");?>
        <div class="admin_list_spinner"><i class="icon-spinner3 spin"></i></div>
        <?$this->includeView("_table");?>
        <?$this->includeView("_footer");?>
        <script type="text/javascript">
            $(function(){
                if(typeof $.adminList == "undefined"){
                    $.adminList = function(listID, obj){
                        if(listID){
                            if(typeof obj == "undefined"){
                                return $(document).data("admin-list-" + listID);
                            }else{
                                $(document).data("admin-list-" + listID, obj)
                                return obj;
                            }
                        }
                    }
                }
                
                var arParams = <?=CJSON::encode($arParams);?>;
                
                $.adminList(arParams.listID, new AdminList(arParams));
            });
        </script>
        <style>
        #<?=$arParams["listID"];?>_wrapper .admin_list_spinner{
            position: absolute;
            z-index: 2;
            width: 100%;
            background: rgba(255,255,255,0.5);
            height: 100%;
            top: 0;
            left: 0;
            display: none;
        }
        
        #<?=$arParams["listID"];?>_wrapper .admin_list_spinner > i{
            position: absolute;
            left: 50%;
            margin: -8px 0 0 -8px;
            top: 10%;
        }
        
        #<?=$arParams["listID"];?>_wrapper{
            position: relative;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        
        #<?=$arParams["listID"];?>_per_page,
        #<?=$arParams["listID"];?>_per_page_title{
            display: inline-block;
            vertical-align: top;
        }
        
        #<?=$arParams["listID"];?>_per_page{
            max-width: 100px;
        }
        
        #<?=$arParams["listID"];?>_per_page_title{
            line-height: 34px;
            margin-right: 15px;
        }
        
        #<?=$arParams["listID"];?>_wrapper .panel-heading{
            padding: 5px 12px;
        }
        </style>
    </div>
</div>