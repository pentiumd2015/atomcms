<?
CEvent::on("CORE.ROUTER.BEFORE", function($obApp, $obRouter){
    if(($arRoutes = include(__DIR__ . "/routes.php")) && is_array($arRoutes)){
        $obRouter->addRoutes($arRoutes);
    }
});

CEvent::on("MENU.SIDEBAR.BEFORE.RENDER", function($obMenu){
    /*add settings.entities*/
    
    foreach($obMenu->items AS &$arItem){
        if($arItem["alias"] == "settings"){
            if(!is_array($arItem["items"])){
                $arItem["items"] = array();
            }
            
            $arItem["items"][] = array(
                "title" => "Сущности",
                "alias" => "settings.entities",
                "icon"  => "icon-tree",
                "items" => array(
                    array(
                        "alias"     => "settings.entity.groups",
                        "priority"  => 1000,
                        "title"     => "Группы сущностей",
                        "link"      => "/admin/settings/entity_groups/",
                        "extraLinks"=> array("/admin/settings/entity_groups/.*"),
                    ),
                    array(
                        "alias"     => "settings.entities.list",
                        "priority"  => 1001,
                        "title"     => "Сущности",
                        "link"      => "/admin/settings/entities/",
                        "extraLinks"=> array("/admin/settings/entities/.*"),
                    ),
                )
            );
            
            break;
        }
    }
    /*add settings.entities*/
    
    /*add entities*/
    $arEntities = \Entities\Entity::findAll(array(
        "order" => "priority ASC"
    ));
    
    $arGroupItems = array();
    
    foreach($arEntities AS $obEntity){
        $arGroupItems[$obEntity->entity_group_id][] = array(
            "priority"      => $obEntity->priority,
            "title"         => $obEntity->title,
            "link"          => "/admin/entity_elements/" . $obEntity->entity_id . "/",
            "extraLinks"    => array("/admin/entity_elements/" . $obEntity->entity_id . "/.*"),
        );
    }
    
    $arEntityGroups = \Entities\EntityGroup::findAll(array(
        "order" => "title ASC"
    ));
    
    $arItems = array();
    
    $index = 500;
    
    foreach($arEntityGroups AS $obEntityGroup){
        if(!isset($arGroupItems[$obEntityGroup->entity_group_id])){
            continue;
        }
        
        $arItems[] = array(
            "title"     => $obEntityGroup->title,
            "priority"  => $index,
            "items"     => $arGroupItems[$obEntityGroup->entity_group_id]
        );
        
        $index++;
    }
    
    $obMenu->items[] = array(
        "title"         => "Контент",
        "priority"      => $index,
        "items"         => $arItems
    );
     /*add entities*/
});
?>