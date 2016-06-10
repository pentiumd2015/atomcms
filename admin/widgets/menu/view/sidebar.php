<?
    if(count($obMenu->getItems())){
        ?>
            <ul class="navigation">
                <?=$obMenu->renderMenu();?>
            </ul>
        <?
    }
?>