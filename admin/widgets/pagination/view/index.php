<?
//p($this->getParams());
$obPagination   = $this->getParam("obPagination");
$urlPageKey     = $this->getParam("urlPageKey");
$path           = $this->getParam("urlPath");

$range = 3;

$pageUrlPattern = CUrl::to($path, [$urlPageKey => "{PAGE_INDEX}"]);

$arTmpQuery = $_GET;
unset($arTmpQuery[$urlPageKey]);
$query          = CUrl::to(null, $arTmpQuery, true);
$firstPageURL   = $path . (strlen($query) ? "?" . $query : ""); //if page=1 unset param page

if($obPagination->numPage > 1){
    ?>
  		<ul class="pagination">
            <?
                if($obPagination->page > 1){
                    ?>
                        <li class="prev">
                            <a href="<?=((($obPagination->page - 1) > 1) ? str_replace("{PAGE_INDEX}", $obPagination->page - 1, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=($obPagination->page - 1);?>">«</a>
                        </li>
                    <?
                }else{
                    ?>
                        <li class="prev disabled">
                            <a>«</a>
                        </li>
                    <?
                }
                
                if($range > 0 && $obPagination->numPage > $range){
                    if($obPagination->page < $range){
                        for($i=1;$i<=$range;$i++){
                            ?>
                                <li<?=($obPagination->page == $i ? ' class="active"' : '');?>>
                                    <a href="<?=(($i > 1) ? str_replace("{PAGE_INDEX}", $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=$i;?>"><?=$i;?></a>
                                </li>
                            <?
                        }
                    }else if($obPagination->page >= $range && $obPagination->page - 1 <= $obPagination->numPage - $range){ //если в середине
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
                                    <a href="<?=((($obPagination->page - $i) > 1) ? str_replace("{PAGE_INDEX}", $obPagination->page - $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=($obPagination->page - $i);?>"><?=$obPagination->page - $i;?></a>
                                </li>
                            <?
                        }
                        
                        //current
                        ?>
                            <li class="active">
                                <a href="<?=str_replace("{PAGE_INDEX}", $obPagination->page, $pageUrlPattern);?>" data-page="<?=$obPagination->page;?>"><?=$obPagination->page;?></a>
                            </li>
                        <?                                
                        
                        //next
                        for($i=1;$i<=$n;$i++){
                            ?>
                                <li>
                                    <a href="<?=str_replace("{PAGE_INDEX}", $obPagination->page + $i, $pageUrlPattern);?>" data-page="<?=($obPagination->page + $i);?>"><?=$obPagination->page + $i;?></a>
                                </li>
                            <?
                        }
                        
                        ?>                                    
                            <li class="disabled">
                                <a>...</a>
                            </li>
                            <li>
                                <a href="<?=str_replace("{PAGE_INDEX}", $obPagination->numPage, $pageUrlPattern);?>" data-page="<?=$obPagination->numPage;?>"><?=$obPagination->numPage;?></a>
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
                        for($i=$obPagination->numPage - $range + 1;$i<=$obPagination->numPage;$i++){
                            ?>
                                <li<?=($obPagination->page == $i ? ' class="active"' : '');?>>
                                    <a href="<?=str_replace("{PAGE_INDEX}", $i, $pageUrlPattern);?>" data-page="<?=$i;?>"><?=$i;?></a>
                                </li>
                            <?
                        }
                    }
                }else{
                    for($i=1;$i<=$obPagination->numPage;$i++){
                        ?>
                            <li<?=($obPagination->page == $i ? ' class="active"' : '');?>>
                                <a href="<?=(($i > 1) ? str_replace("{PAGE_INDEX}", $i, $pageUrlPattern) : $firstPageURL);?>" data-page="<?=$i;?>"><?=$i;?></a>
                            </li>
                        <?
                    }
                }
                
                if($obPagination->page < $obPagination->numPage){
                    ?>
                        <li class="next">
                            <a href="<?=str_replace("{PAGE_INDEX}", $obPagination->page + 1, $pageUrlPattern);?>" data-page="<?=($obPagination->page + 1);?>">»</a>
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