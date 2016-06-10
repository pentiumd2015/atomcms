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
                CTemplate::update($arGroupItems, array("active" => 1));
                break;
            case "deactivate":
                CTemplate::update($arGroupItems, array("active" => 0));
                break;
            case "delete":
                CTemplate::delete($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/

$arTemplates = CTemplate::getList();
$arTemplates = $obPagination->initFromArray($arTemplates);

/*Apply Request Sort*/
if($_REQUEST["sort"]){
    $sortField  = $_REQUEST["sort"];
    
    $sortBy     = htmlspecialchars($_REQUEST["by"]);
    $sortBy     = $sortBy == "asc" ? SORT_ASC : SORT_DESC ;
            
    switch($sortField){
        case "template_id":
        case "title":
        case "path":
        case "description":
            $arTemplates = CArraySorter::subSort($arTemplates, $sortField, $sortBy);
            
            break;
    }
}
/*Apply Request Filter*/
if(is_array($_REQUEST["f"])){
    foreach($_REQUEST["f"] AS $field => $value){
        switch($field){
            case "template_id":
            case "title":
            case "domains":
                if(is_string($value) && strlen($value)){
                    
                    $arTemplates = CArrayFilter::filter($arTemplates, function($arSite) use($field, $value){
                        return stripos($arSite[$field], $value) !== false;
                    });
                }
                break;
            case "active":
                if(strlen($value) && ($value == 1 || $value == 0)){
                    $arTemplates = CArrayFilter::filter($arTemplates, function($arSite) use($field, $value){
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
        "field"     => "template_id"
    ),
    array(
        "title"     => "Название",
        "field"     => "title"
    ),
    array(
        "title"     => "Путь до папки",
        "field"     => "path"
    ),
    array(
        "title"     => "Описание",
        "field"     => "description",
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
        "title" => "Удалить",
        "value" => "delete"
    )
));
/*Entity Elements List*/


\Page\Breadcrumbs::add(array(
    $listURL => "Список шаблонов"
));

$this->setData(array(
    "tableID"                   => $tableID,
    "obAdminTableListFilter"    => $obAdminTableListFilter,
    "obAdminTableList"          => $obAdminTableList,
    "arDisplayList"             => $arDisplayList,
    
    "arTemplates"               => $arTemplates,
    "addURL"                    => $addURL,
    "editURL"                   => $editURL,
    "listURL"                   => $listURL,
    "templatePath"              => \Helpers\CFile::normalizePath($this->app("template")->templatePath . "/{TEMPLATE_PATH}")
));

$this->includeView("list");
?>