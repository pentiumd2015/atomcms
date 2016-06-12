<?
use Helpers\CBuffer;

class SidebarMenu{
    public $items = array();
    
    public function __construct($url = NULL){
        $this->url = $url;
    }
    
    public function getItems(){
        return $this->items;
    }
    
    public function setItems($arItems){
        return $this->items = $arItems;
    }
    
    public function addItem($arItem){
        return $this->items[] = $arItem;
    }
    
    public function renderMenu(){
        $arItems = $this->_recursiveMenuSort($this->items);
        
        return $this->_recursiveRenderMenu($arItems);
    }
    
    protected function _recursiveRenderMenu($arMenu, $depthLevel = 1){
        CBuffer::start();

            foreach($arMenu AS $arItem){
                $active = false;
                
                if(isset($arItem["link"])){
                    $active = $this->checkActiveMenuLink($arItem["link"]);
                }
                
                if(!$active && isset($arItem["extraLinks"]) && is_array($arItem["extraLinks"])){
                    $active = $this->checkActiveMenuLink($arItem["extraLinks"]);
                }
                ?>
                    <li<?=($active ? ' class="active"' : "")?>>
                        <?
                            if(isset($arItem["items"]) && count($arItem["items"])){
                                ?>
                                    <a href="#" class="expand"<?=($depthLevel > 1 ? 'style="padding-left: ' . (30 * ($depthLevel - 1)) . 'px;"' : "")?>>
                                        <span><?=$arItem["title"];?></span>
                                        <?
                                            if($arItem["icon"]){
                                                ?>
                                                    <i class="<?=$arItem["icon"];?>"></i>
                                                <?
                                            }
                                        ?>
                                    </a>
                                    <ul><?=$this->_recursiveRenderMenu($arItem["items"], $depthLevel + 1);?></ul>
                                <?
                            }else{
                                ?>
                                    <a href="<?=(isset($arItem["link"]) ? $arItem["link"] : "");?>"<?=($depthLevel > 1 ? 'style="padding-left: ' . (25 * ($depthLevel - 1)) . 'px;"' : "")?>>
                                        <span><?=$arItem["title"];?></span>
                                        <?
                                            if(isset($arItem["icon"])){
                                                ?>
                                                    <i class="<?=$arItem["icon"];?>"></i>
                                                <?
                                            }
                                        ?>
                                    </a>
                                <?
                            }
                        ?>
                    </li>
                <?
            }
        
        return CBuffer::end();
    }
    
    protected function _recursiveMenuSort($arItems, $sortKey = "priority"){
        usort($arItems, function($a, $b) use($sortKey){
            if($a[$sortKey] == $b[$sortKey]){
                return 0;
            }else{
                return $a[$sortKey] <= $b[$sortKey] ? -1 : 1 ;
            }
        });
        
        foreach($arItems AS &$arItem){
            if(isset($arItem["items"])){
                $arItem["items"] = $this->_recursiveMenuSort($arItem["items"], $sortKey);
            }
        }
        
        return $arItems;
    }
    
    public function checkActiveMenuLink($menuLink){
        if(is_array($menuLink)){
            $arMenuLinks    = $menuLink;
            $hasActive      = false;
            
            foreach($arMenuLinks AS $menuLink){
                if($this->_checkActiveMenuLink($menuLink) || preg_match("#^" . $menuLink . "$#si", $this->url)){
                    return true;
                }
            }
            
            return false;
        }else{
            return $this->_checkActiveMenuLink($menuLink) || preg_match("#^" . $menuLink . "$#si", $this->url);
        }
    }
    
    protected function _checkActiveMenuLink($menuLink){
        $arResult = array();
        
        if(($pos = strpos($menuLink, "?")) !== false){
            $arResult = parse_url($menuLink);
            
            if($arResult["query"]){
                parse_str($arResult["query"], $arResult["query"]);
            }
        }else{
            $arResult = array("path" => $menuLink);
        }
        
        if($arResult["path"] != $this->url){
            return false;
        }
        
        if(isset($arResult["query"])){
            return $arResult["query"] == array_intersect_assoc($arResult["query"], $_GET);
        }
        
        return true;
    }
}
?>