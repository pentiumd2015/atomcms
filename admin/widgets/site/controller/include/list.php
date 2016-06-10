<?
use \Helpers\CHttpResponse;
use \Helpers\CArrayHelper;
use \Helpers\CArrayFilter;
use \Helpers\CArraySorter;
use \Helpers\CJSON;

/*PerPage*/
$arPerPage = array(
    20  => "Показать по 20",
    50  => "Показать по 50",
    100 => "Показать по 100",
    -1  => "Показать все"
);

$perPage = key($arPerPage);

if($_REQUEST["perPage"] && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = (int)$_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        switch($_REQUEST["group"]){
            case "activate":
                CSite::update($arGroupItems, array("active" => 1));
                break;
            case "deactivate":
                CSite::update($arGroupItems, array("active" => 0));
                break;
            case "delete":
                CSite::delete($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/

$arSites = CSite::getList();
$arSites = $obPagination->initFromArray($arSites);

/*Apply Request Sort*/
if($_REQUEST["sort"]){
    $sortField  = $_REQUEST["sort"];
    
    $sortBy     = htmlspecialchars($_REQUEST["by"]);
    $sortBy     = $sortBy == "asc" ? SORT_ASC : SORT_DESC ;
            
    switch($sortField){
        case "site_id":
        case "title":
        case "domains":
        case "active":
            $arSites = CArraySorter::subSort($arSites, $sortField, $sortBy);
            
            break;
    }
}
/*Apply Request Filter*/
if(is_array($_REQUEST["f"])){
    foreach($_REQUEST["f"] AS $field => $value){
        switch($field){
            case "site_id":
            case "title":
            case "domains":
                if(is_string($value) && strlen($value)){
                    
                    $arSites = CArrayFilter::filter($arSites, function($arSite) use($field, $value){
                        return stripos($arSite[$field], $value) !== false;
                    });
                }
                break;
            case "active":
                if(strlen($value) && ($value == 1 || $value == 0)){
                    $arSites = CArrayFilter::filter($arSites, function($arSite) use($field, $value){
                        if($value){
                            return $arSite[$field] == 1 ? true : false ;
                        }else{
                            return $arSite[$field] == 0 ? true : false ;
                        }
                    });
                }
                break;
        }
    }
}
/*Apply Request Filter*/

/*Filter*/
$filterID = "sites";

$obAdminTableListFilter = new \Admin\CAdminTableListFilter(array(
    "filterID"  => $filterID,
    "onApply"   => "onApplyFilter"
));
/*Filter*/

/*List*/
$tableID = "sites";

$obAdminTableList = new \Admin\CAdminTableList(array(
    "tableID"           => $tableID,
    "tableAttributes"   => array("class" => "table table-striped table-bordered table-hover"),
    "url"               => $listURL,
    "onApplyAfter"      => "onApplyTableList"
));

$obAdminTableList->setPerPage($arPerPage);
$obAdminTableList->addPagination($obPagination);


$arDisplayList = array(
    array(
        "title"     => "ID",
        "field"     => "site_id"
    ),
    array(
        "title"     => "Название",
        "field"     => "title"
    ),
    array(
        "title"     => "Домены",
        "field"     => "domains"
    ),
    array(
        "title"     => "Активность",
        "field"     => "active",
    )
);

$arHeaders = array();

foreach($arDisplayList AS $arField){
    $arHeaders[] = array(
        "title"     => $arField["title"],
        "sortable"  => true,
        "field"     => $arField["field"]
    );
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
/*Entity Elements List*/


\Page\Breadcrumbs::add(array(
    $listURL => "Список сайтов"
));

$this->setData(array(
    "tableID"                   => $tableID,
    "obAdminTableListFilter"    => $obAdminTableListFilter,
    "obAdminTableList"          => $obAdminTableList,
    "arDisplayList"             => $arDisplayList,
    
    "arSites"                   => $arSites,
    "addURL"                    => $addURL,
    "editURL"                   => $editURL,
    "listURL"                   => $listURL
));

$this->includeView("list");
?>