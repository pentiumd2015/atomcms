<?
use \Entity\Field\BaseField;

$arParams = $this->getParams();

$arParams["formID"]     = isset($arParams["formID"]) ? $arParams["formID"] : "detail";
$arParams["data"]       = isset($arParams["data"]) && is_array($arParams["data"]) ? $arParams["data"] : [] ;
$arParams["formData"]   = isset($arParams["formData"]) && is_array($arParams["formData"]) ? $arParams["formData"] : [] ;
$arParams["attributes"] = isset($arParams["attributes"]) && is_array($arParams["attributes"]) ? $arParams["attributes"] : ["class" => "form-horizontal"] ;
$arParams["buttons"]    = isset($arParams["buttons"]) ? $arParams["buttons"] : CHtml::button("Сохранить", [
    "type"  => "submit",
    "class" => "btn btn-primary"
]);

$arParams["url"]        = isset($arParams["url"]) ? $arParams["url"] : $this->app("route")->url;
$arParams["tabs"]       = isset($arParams["tabs"]) && is_array($arParams["tabs"]) ? $arParams["tabs"] : [] ;

$arParams["attributes"] = array_merge($arParams["attributes"], [
    "id"        => $arParams["formID"],
    "method"    => "POST",
    "action"    => $arParams["url"]
]);

foreach($arParams["tabs"] AS $tabIndex => $arTab){
    if(isset($arTab["fields"]) && is_array($arTab["fields"])){
        foreach($arTab["fields"] AS $fieldIndex => $obField){
            if(!$obField instanceof BaseField){
                unset($arParams["tabs"][$tabIndex]["fields"][$fieldIndex]);
            }
        }
    }else{
        $arParams["tabs"][$tabIndex]["fields"] = [];
    }
}

$arRendererParams = [
    "requestName" => $arParams["formID"]
];

$this->setData([
    "arParams"          => $arParams,
    "arRendererParams"  => $arRendererParams
]);

$this->includeView();