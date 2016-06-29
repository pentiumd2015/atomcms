<?
use Helpers\CUrl;

//p($this->getParams());
$pagination   = $this->getParam("pagination");
$urlPageKey     = $this->getParam("urlPageKey");
$path           = $this->getParam("urlPath");

$range = 3;

$pageUrlPattern = CUrl::to($path, [$urlPageKey => "{PAGE_INDEX}"]);

$arTmpQuery = $_GET;
unset($arTmpQuery[$urlPageKey]);
$query          = CUrl::to(null, $arTmpQuery, true);
$firstPageURL   = $path . (strlen($query) ? "?" . $query : ""); //if page=1 unset param page

if($pagination->getNumPage() > 1){
    ?>
  		<ul class="pagination">
            <?
                if($pagination->page > 1){
                    ?>
                        <li class="prev">
                            <a href="<?=((($pagination->page - 1) > 1) ? str_replace("{PAGE_INDEX}", $pagination->page - 1, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=($pagination->page - 1);?>">«</a>
                        </li>
                    <?
                }else{
                    ?>
                        <li class="prev disabled">
                            <a>«</a>
                        </li>
                    <?
                }
                
                if($range > 0 && $pagination->getNumPage() > $range){
                    if($pagination->page < $range){
                        for($i=1;$i<=$range;$i++){
                            ?>
                                <li<?=($pagination->page == $i ? ' class="active"' : '');?>>
                                    <a href="<?=(($i > 1) ? str_replace("{PAGE_INDEX}", $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=$i;?>"><?=$i;?></a>
                                </li>
                            <?
                        }
                    }else if($pagination->page >= $range && $pagination->page - 1 <= $pagination->numPage - $range){ //если в середине
                        $n = floor($range / 2);
                        
                        ?>
                            <li>
                                <a href="<?=$firstPageURL;?>">1</a>
                            </li>
                            <li class="disabled">
                                <a>...</a>
                            </li>
                        <?
                        
                        //prev
                        for($i=$n;$i>0;$i--){
                            ?>
                                <li>
                                    <a href="<?=((($pagination->page - $i) > 1) ? str_replace("{PAGE_INDEX}", $pagination->page - $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=($pagination->page - $i);?>"><?=$pagination->page - $i;?></a>
                                </li>
                            <?
                        }
                        
                        //current
                        ?>
                            <li class="active">
                                <a href="<?=str_replace("{PAGE_INDEX}", $pagination->page, $pageUrlPattern);?>" data-page="<?=$pagination->page;?>"><?=$pagination->page;?></a>
                            </li>
                        <?                                
                        
                        //next
                        for($i=1;$i<=$n;$i++){
                            ?>
                                <li>
                                    <a href="<?=str_replace("{PAGE_INDEX}", $pagination->page + $i, $pageUrlPattern);?>" data-page="<?=($pagination->page + $i);?>"><?=$pagination->page + $i;?></a>
                                </li>
                            <?
                        }
                        
                        ?>                                    
                            <li class="disabled">
                                <a>...</a>
                            </li>
                            <li>
                                <a href="<?=str_replace("{PAGE_INDEX}", $pagination->numPage, $pageUrlPattern);?>" data-page="<?=$pagination->numPage;?>"><?=$pagination->numPage;?></a>
                            </li>
                        <?
                    }else{ //если в конце
                        ?>
                            <li>
                                <a href="<?=str_replace("{PAGE_INDEX}", 1, $pageUrlPattern);?>" data-page="1">1</a>
                            </li>
                            <li class="disabled">
                                <a>...</a>
                            </li>
                        <?
                        for($i=$pagination->numPage - $range + 1;$i<=$pagination->numPage;$i++){
                            ?>
                                <li<?=($pagination->page == $i ? ' class="active"' : '');?>>
                                    <a href="<?=str_replace("{PAGE_INDEX}", $i, $pageUrlPattern);?>" data-page="<?=$i;?>"><?=$i;?></a>
                                </li>
                            <?
                        }
                    }
                }else{
                    for($i=1;$i<=$pagination->getNumPage();$i++){
                        ?>
                            <li<?=($pagination->page == $i ? ' class="active"' : '');?>>
                                <a href="<?=(($i > 1) ? str_replace("{PAGE_INDEX}", $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=$i;?>"><?=$i;?></a>
                            </li>
                        <?
                    }
                }
                
                if($pagination->page < $pagination->getNumPage()){
                    ?>
                        <li class="next">
                            <a href="<?=str_replace("{PAGE_INDEX}", $pagination->page + 1, $pageUrlPattern);?>" data-page="<?=($pagination->page + 1);?>">»</a>
                        </li>
                    <?
                }else{
                    ?>
                        <li class="next disabled">
                            <a>»</a>
                        </li>
                    <?
                }
            ?>
  		</ul>
    <?
}