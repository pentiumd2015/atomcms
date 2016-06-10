<?
use \Entity\Field\BaseField;

$arParams = $this->getParams();

$arParams["filterID"]       = isset($arParams["filterID"]) ? $arParams["filterID"] : "filter";
$arParams["data"]           = isset($arParams["data"]) && is_array($arParams["data"]) ? $arParams["data"] : [] ;
$arParams["filterData"]     = isset($arParams["filterData"]) && is_array($arParams["filterData"]) ? $arParams["filterData"] : [] ;
$arParams["attributes"]     = isset($arParams["attributes"]) && is_array($arParams["attributes"]) ? $arParams["attributes"] : ["class" => "form-horizontal list_filter_form"] ;
$arParams["fields"]         = isset($arParams["fields"]) && is_array($arParams["fields"]) ? $arParams["fields"] : [] ;
$arParams["onApplyBefore"]  = isset($arParams["onApplyBefore"]) ? $arParams["onApplyBefore"] : "onApplyFilterBefore" ;
$arParams["onApplyAfter"]   = isset($arParams["onApplyAfter"]) ? $arParams["onApplyAfter"] : "onApplyFilterAfter";
$arParams["url"]            = isset($arParams["url"]) ? $arParams["url"] : $this->app("route")->url;

$arParams["attributes"]     = array_merge($arParams["attributes"], [
    "id"        => $arParams["filterID"],
    "method"    => "POST",
    "action"    => $arParams["url"]
]);

foreach($arParams["fields"] AS $index => $obField){
    if(!$obField instanceof BaseField){
        unset($arParams["fields"][$index]);
    }
}

$arRendererParams = [
    "requestName"   => (isset($arParams["requestName"]) ? $arParams["requestName"] : "f"),
    "filterID"      => $arParams["filterID"]
];

$this->setData([
    "arParams"          => $arParams,
    "arRendererParams"  => $arRendererParams
]);

$this->includeView();