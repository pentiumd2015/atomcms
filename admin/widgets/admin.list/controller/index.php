<?
use \Entity\Field\BaseField;

$arParams = $this->getParams();

$arParams["controls"]               = isset($arParams["controls"]) ? $arParams["controls"] : function(){} ;
$arParams["showHead"]               = isset($arParams["showHead"]) && $arParams["showHead"] != true ? false : true ;
$arParams["listID"]                 = isset($arParams["listID"]) ? $arParams["listID"] : "list";
$arParams["fields"]                 = isset($arParams["fields"]) && is_array($arParams["fields"]) ? $arParams["fields"] : [] ;
$arParams["options"]                = isset($arParams["options"]) && is_array($arParams["options"]) ? $arParams["options"] : [] ;
$arParams["onRowOptions"]           = isset($arParams["onRowOptions"]) && is_callable($arParams["onRowOptions"]) ? $arParams["onRowOptions"] : null ;
$arParams["onCellOptions"]          = isset($arParams["onCellOptions"]) && is_callable($arParams["onCellOptions"]) ? $arParams["onCellOptions"] : null ;
$arParams["listData"]               = isset($arParams["listData"]) && is_array($arParams["listData"]) ? $arParams["listData"] : [] ;
$arParams["tableAttributes"]        = isset($arParams["tableAttributes"]) && is_array($arParams["tableAttributes"]) ? $arParams["tableAttributes"] : ["class" => "table table-striped table-bordered table-hover"] ;
$arParams["headAttributes"]         = isset($arParams["headAttributes"]) && is_array($arParams["headAttributes"]) ? $arParams["headAttributes"] : [] ;
$arParams["bodyAttributes"]         = isset($arParams["bodyAttributes"]) && is_array($arParams["bodyAttributes"]) ? $arParams["bodyAttributes"] : [] ;
$arParams["groupKey"]               = isset($arParams["groupKey"]) ? $arParams["groupKey"] : "group" ;
$arParams["sortKey"]                = isset($arParams["sortKey"]) ? $arParams["sortKey"] : "sort" ;
$arParams["sortByKey"]              = isset($arParams["sortByKey"]) ? $arParams["sortByKey"] : "by" ;
$arParams["pageKey"]                = isset($arParams["pageKey"]) ? $arParams["pageKey"] : "page" ;
$arParams["perPageKey"]             = isset($arParams["perPageKey"]) ? $arParams["perPageKey"] : "perPage" ;
$arParams["baseURL"]                = isset($arParams["baseURL"]) ? $arParams["baseURL"] : $this->app("route")->url ;
$arParams["onApplyGroupOperation"]  = isset($arParams["onApplyGroupOperation"]) ? $arParams["onApplyGroupOperation"] : "" ;
$arParams["onChangeGroupOperation"] = isset($arParams["onChangeGroupOperation"]) ? $arParams["onChangeGroupOperation"] : "" ;
$arParams["onApplyBefore"]          = isset($arParams["onApplyBefore"]) ? $arParams["onApplyBefore"] : "onApplyListBefore" ;
$arParams["onApplyAfter"]           = isset($arParams["onApplyAfter"]) ? $arParams["onApplyAfter"] : "onApplyListAfter" ;
$arParams["pagination"]             = isset($arParams["pagination"]) && $arParams["pagination"] instanceof CPagination ? $arParams["pagination"] : null ;
$arParams["groupOperationsContent"] = isset($arParams["groupOperationsContent"]) ? $arParams["groupOperationsContent"] : null ;
$arParams["perPageList"]            = isset($arParams["perPageList"]) && is_array($arParams["perPageList"]) && count($arParams["perPageList"]) ? $arParams["perPageList"] : [
    20  => "20",
    50  => "50",
    100 => "100",
    -1  => "Все"
];

$arParams["groupOperations"]        = isset($arParams["groupOperations"]) && is_array($arParams["groupOperations"]) ? $arParams["groupOperations"] : [
    [
        "title" => "Активировать",
        "value" => "activate"
    ],
    [
        "title" => "Деактивировать",
        "value" => "deactivate"
    ],
    [
        "title" => "Удалить",
        "value" => "delete"
    ]
];

$arParams["tableAttributes"] = array_merge($arParams["tableAttributes"], [
    "id" => $arParams["listID"]
]);

$arParams["headAttributes"] = array_merge($arParams["headAttributes"], [
    "id" => $arParams["listID"] . "_thead"
]);

$arParams["bodyAttributes"] = array_merge($arParams["bodyAttributes"], [
    "id" => $arParams["listID"] . "_tbody"
]);

$pk = "id";

if(!isset($arParams["primaryKey"])){
    if(count($arParams["fields"])){
        $pk = reset($arParams["fields"])->getDispatcher()
                                        ->getBuilder()
                                        ->getEntity()
                                        ->getPk();
    }
}else{
    $pk = $arParams["primaryKey"];
}

$arParams["options"]["primaryKey"] = $pk;

foreach($arParams["fields"] AS $index => $obField){
    if(!$obField instanceof BaseField){
        unset($arParams["fields"][$index]);
    }
}

$this->setData([
    "arParams" => $arParams
]);

$this->includeView();