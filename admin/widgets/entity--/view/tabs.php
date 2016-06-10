<?
$arTabs = array(
    array(
        "alias" => "main",
        "icon"  => "icon-wrench",
        "title" => "Основные параметры",
        "link"  => $editURL
    ),
    array(
        "alias" => "fields",
        "icon"  => "icon-list",
        "title" => "Поля",
        "link"  => $editURL . "fields/"
    ),
    array(
        "alias" => "display",
        "icon"  => "icon-table2",
        "title" => "Отображение",
        "link"  => $editURL . "display/"
    ),
    array(
        "alias" => "signature",
        "icon"  => "icon-quill2",
        "title" => "Подписи",
        "link"  => $editURL . "signature/"
    ),
    array(
        "alias" => "access",
        "icon"  => "icon-user",
        "title" => "Доступ",
        "link"  => $editURL . "access/"
    ),
);
?>
<ul class="nav nav-tabs">
    <?
        foreach($arTabs AS $arTab){
            $isActive = false;
            
            if($arTab["items"]){
                foreach($arTab["items"] AS $arItem){
                    if($arItem["alias"] == $active){
                        $isActive = true;
                        break;
                    }
                }
            }else if($arTab["alias"] == $active){
                $isActive = true;
            }
            
            ?>
                <li<?=($isActive ? '  class="active"' : "");?>>
                    <?
                        if($arTab["items"]){
                            ?>
                                <a<?=(!$isActive ? ' href="' . $arTab["link"] . '"' : "")?> class="dropdown-toggle" data-toggle="dropdown"><?=($arTab["icon"] ? '<i class="' . $arTab["icon"] . '"></i> ' : "") . $arTab["title"];?> <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <?
                                        foreach($arTab["items"] AS $arItem){
                                            ?>
                                                <li<?=($arItem["alias"] == $active ? '  class="active"' : "");?>>
                                                    <a<?=($arItem["alias"] != $active ? ' href="' . $arItem["link"] . '"' : "")?>><?=($arItem["icon"] ? '<i class="' . $arItem["icon"] . '"></i> ' : "") . $arItem["title"];?></a>
                                                </li>
                                            <?
                                        }
                                    ?>
                                </ul>
                            <?
                        }else{
                            ?>
                                <a<?=(!$isActive ? ' href="' . $arTab["link"] . '"' : "")?>><?=($arTab["icon"] ? '<i class="' . $arTab["icon"] . '"></i> ' : "") . $arTab["title"];?></a>
                            <?
                        }
                    ?>
                </li>
            <?
        }
    ?>
</ul>