<?
use \Models\UserGroupAccess;

/*PerPage*/
$arPerPage = array(
    20   => "20",
    50  => "50",
    100 => "100",
    -1  => "Все"
);

$perPage = 20;

if(isset($_REQUEST["perPage"]) && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = $_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/


$arSqlParams["params"] = array(
    "select"        => "t1.*",
    "alias"         => "t1",
    "pagination"    => $obPagination,
    "order"         => "t1.user_group_access_id DESC"
);

$arSqlParams["statements"] = array();

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        switch($_REQUEST["group"]){
            case "delete":
                UserGroupAccess::deleteAllByPk($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/

/*Apply Request Sort*/
if($_REQUEST["sort"]){
    $sortField  = $_REQUEST["sort"];
    
    $sortBy     = htmlspecialchars($_REQUEST["by"]);
    $sortBy     = $sortBy == "asc" ? "ASC" : "DESC" ;
    
    switch($sortField){
        case "user_group_access_id":
        case "title":
        case "alias":
        case "description":
            $arSqlParams["params"]["order"] = "t1." . $sortField . " " . $sortBy;
            break;
    }
}
/*Apply Request Sort*/

/*Apply Request Filter*/
if(is_array($_REQUEST["f"])){
    if(!$arSqlParams["params"]["condition"]){
        $arSqlParams["params"]["condition"] = "1=1";
    }
    
    foreach($_REQUEST["f"] AS $field => $value){
        switch($field){
            case "user_group_access_id":
                if(strpos($value, "-") !== false){
                    list($idFrom, $idTo) = explode("-", $value, 2);
                    
                    if($idFrom && $idTo){
                        $idFrom = (int)$idFrom;
                        $idTo   = (int)$idTo;
                        
                        if($idFrom > $idTo){
                            $tmpID  = $idTo;
                            $idTo   = $idFrom;
                            $idFrom = $tmpID;
                        }
                        
                        $arSqlParams["params"]["condition"].= "\nAND (t1." . $field . ">=" . $idFrom . " AND t1." . $field . "<=" . $idTo . ")";
                    }else if($idFrom){
                        $arSqlParams["params"]["condition"].= "\nAND (t1." . $field . ">=" . (int)$idFrom . ")";
                    }else if($idTo){
                        $arSqlParams["params"]["condition"].= "\nAND (t1." . $field . "<=" . (int)$idTo . ")";
                    }
                }else if(strpos($value, ",") !== false){
                     $arItemIDs = explode(",", $value);
                     array_walk($arItemIDs, "trim");
                     array_walk($arItemIDs, "intval");
                     
                     $arItemIDs = array_filter($arItemIDs);
                     
                     if(count($arItemIDs)){
                        $arSqlParams["params"]["condition"].= "\nAND (t1." . $field . " IN(" . implode(", ", $arItemIDs) . "))";
                     }
                }else if($value){
                    $itemID = (int)$value;
                    
                    $arSqlParams["params"]["condition"].= "\nAND (t1." . $field . "=?)";
                    $arSqlParams["statements"][] = $itemID;
                }
                break;
            case "title":
            case "alias":
                if(is_string($value) && strlen($value)){
                    $arSqlParams["params"]["condition"].= "\nAND t1." . $field . " LIKE ?";
                    $arSqlParams["statements"][] = "%" . $value . "%";
                }
                break;
        }
    }
}
/*Apply Request Filter*/

$arUserGroupAccess = UserGroupAccess::findAll($arSqlParams["params"], $arSqlParams["statements"]);

/*Filter*/
$filterID = "user_group_access";

$obAdminTableListFilter = new \Admin\CAdminTableListFilter(array(
    "filterID"  => $filterID,
    "onApply"   => "onApplyFilter"
));
/*Filter*/

$tableID = "user_group_access";

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
        "field"     => "user_group_access_id"
    ),
    array(
        "title"     => "Название",
        "field"     => "title"
    ),
    array(
        "title"     => "Алиас",
        "field"     => "alias",
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

$this->setData(array(
    "tableID"                   => $tableID,
    "obAdminTableListFilter"    => $obAdminTableListFilter,
    "obAdminTableList"          => $obAdminTableList,
    "arDisplayList"             => $arDisplayList,
    
    "arUserGroupAccess" => $arUserGroupAccess,
    "addURL"            => $addURL,
    "editURL"           => $editURL,
    "listURL"           => $listURL,
    "obPagination"      => $obPagination,
));



$this->includeView("list");
?>