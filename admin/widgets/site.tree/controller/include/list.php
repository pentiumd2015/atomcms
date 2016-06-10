<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CArrayFilter;
use \Helpers\CArraySorter;
use \Helpers\CJSON;

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        $arSiteRoutes = array();
        
        foreach($arGroupItems AS $groupItem){
            list($siteID, $routePath) = explode("#", $groupItem, 2);
            
            $arSiteRoutes[$siteID][] = $routePath;
        }
        
        switch($_REQUEST["group"]){
            case "activate":
                foreach($arSiteRoutes AS $siteID => $arRoutes){
                    CRouter::update($siteID, $arRoutes, array("active" => 1));
                }
                
                break;
            case "deactivate":
                foreach($arSiteRoutes AS $siteID => $arRoutes){
                    CRouter::update($siteID, $arRoutes, array("active" => 0));
                }
                
                break;
            case "delete":
                foreach($arSiteRoutes AS $siteID => $arRoutes){
                    CRouter::delete($siteID, $arRoutes);
                }
                
                break;
        }
    }
}
/*Apply Request Group*/
$arSiteRoutes = CRouter::getList();

/*Apply Request Filter*/

if(is_array($_REQUEST["f"])){
    foreach($_REQUEST["f"] AS $field => $value){
        switch($field){
            case "site_id":
                foreach($arSiteRoutes AS $siteID => $arRoutes){
                    if($value != $siteID){
                        unset($arSiteRoutes[$siteID]);
                    }
                }
                break;
            case "title":
            case "path":
                if(is_string($value) && strlen($value)){
                    foreach($arSiteRoutes AS $siteID => &$arRoutes){
                        $arRoutes = CArrayFilter::filter($arRoutes, function($arSite) use($field, $value){
                            return stripos($arSite[$field], $value) !== false;
                        });
                    }
                    
                    unset($arRoutes);
                }
                break;
            case "active":
                if(strlen($value) && ($value == 1 || $value == 0)){
                    foreach($arSiteRoutes AS $siteID => &$arRoutes){
                        $arRoutes = CArrayFilter::filter($arRoutes, function($arSite) use($field, $value){
                            if($value){
                                return $arSite[$field] == 1 ? true : false ;
                            }else{
                                return $arSite[$field] == 0 ? true : false ;
                            }
                        });
                    }
                    
                    unset($arRoutes);
                }
                break;
        }
    }
}/**/
/*Apply Request Filter*/


$arSites = CSite::getList();

/*Filter*/
$filterID = "site_tree";

$obAdminTableListFilter = new \Admin\CAdminTableListFilter(array(
    "filterID"  => $filterID,
    "onApply"   => "onApplyFilter"
));
/*Filter*/

/*List*/
$tableID = "site_tree";

$obAdminTableList = new \Admin\CAdminTableList(array(
    "tableID"           => $tableID,
    "tableAttributes"   => array("class" => "table table-bordered"),
    "url"               => $listURL,
    "onApplyAfter"      => "onApplyTableList"
));

$obAdminTableList->setPerPage($arPerPage);
$obAdminTableList->addPagination($obPagination);


$arDisplayList = array(
    array(
        "title"     => "Путь",
        "field"     => "path"
    ),
    array(
        "title"     => "Название",
        "field"     => "title"
    ),
    array(
        "title"     => "Сайт",
        "field"     => "site_id"
    ),
    array(
        "title"     => "Активность",
        "field"     => "active",
    )
);

$arHeaders = array();

foreach($arDisplayList AS $arField){
    switch($arField["field"]){
        case "title":
        case "path":
        case "active":
            $arHeaders[] = array(
                "title"     => $arField["title"],
                "sortable"  => false,
                "field"     => $arField["field"]
            );
            break;
    }
}

$arHeaders[] = array(
    "title"         => "Действие",
    "attributes"    => array(
        "style" => "width: 125px;"
    )
);
    
$obAdminTableList->addHeaders($arHeaders);

$obAdminTableList->addGroupOperations(array(
    array(
        "title" => "Активировать",
        "value" => "activate"
    ),
    array(
        "title" => "Деактивировать",
        "value" => "deactivate"
    ),
    array(
        "title" => "Удалить",
        "value" => "delete"
    )
));

\Page\Breadcrumbs::add(array(
    $listURL => "Дерево сайта"
));

$this->setData(array(
    "tableID"                   => $tableID,
    "obAdminTableListFilter"    => $obAdminTableListFilter,
    "obAdminTableList"          => $obAdminTableList,
    "arDisplayList"             => $arDisplayList,
    "arSites"                   => $arSites,
    "arSiteRoutes"              => $arSiteRoutes,
    "addURL"                    => $addURL,
    "editURL"                   => $editURL,
    "listURL"                   => $listURL
));

$this->includeView("list");
?>