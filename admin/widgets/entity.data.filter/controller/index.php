<?
use Entity\Manager;
use Helpers\CJson;

$params = $this->getParams();

$params["entity"] = isset($params["entity"]) ? $params["entity"] : null ;

if($params["entity"] && class_exists($params["entity"])){
    $params["entity"] = new $params["entity"];
}

if(!$params["entity"] instanceof Manager){
    throw new CException("Entity is required for widget entity.data.filter");
}

$params["settingsUrl"]      = isset($params["settingsUrl"]) ? $params["settingsUrl"] : null;
$params["filterId"]         = isset($params["filterId"]) ? $params["filterId"] : "filter";
$params["data"]             = isset($params["data"]) && is_array($params["data"]) ? $params["data"] : [] ;
$params["filterData"]       = isset($params["filterData"]) && is_array($params["filterData"]) ? $params["filterData"] : [] ;
$params["attributes"]       = isset($params["attributes"]) && is_array($params["attributes"]) ? $params["attributes"] : ["class" => "form-horizontal list_filter_form"] ;
$params["fields"]           = isset($params["fields"]) && is_array($params["fields"]) ? $params["fields"] : [] ;
$params["onApplyBefore"]    = isset($params["onApplyBefore"]) ? $params["onApplyBefore"] : "onApplyFilterBefore" ;
$params["onApplyAfter"]     = isset($params["onApplyAfter"]) ? $params["onApplyAfter"] : "onApplyFilterAfter";
$params["url"]              = isset($params["url"]) ? $params["url"] : CAtom::$app->route->url;
$params["attributes"]       = array_merge($params["attributes"], [
    "id"        => $params["filterId"],
    "method"    => "POST",
    "action"    => $params["url"]
]);

$params["rendererParams"] = [
    "requestName"   => (isset($params["requestName"]) ? $params["requestName"] : "f"),
    "filterId"      => $params["filterId"]
];

$params["jsonParams"] = CJson::encode($params);

$fields = $params["entity"]->query()->getFields();

$tmpFields = [];

foreach($params["fields"] AS $fieldName => $field){
    if(is_numeric($fieldName)){
        if(is_string($field)){
            $fieldName = $field;

            if(isset($fields[$fieldName]) && $fields[$fieldName]->filterable){
                $fieldRenderer = $fields[$fieldName]->getRenderer();

                $tmpFields[$fieldName] = ["renderer" => [$fieldRenderer, "renderFilter"]];
            }
        }
    }else{
        if(isset($field["renderer"]) && is_callable($field["renderer"])){
            $tmpFields[$fieldName] = $field;
        }else{
            throw new CException("Field Renderer not set in 'renderer' key of fields array");
        }
    }
}

$params["fields"] = $tmpFields;

$this->view->addJs(BASE_URL . $this->path . "js/script.js");

$this->setViewData($params);

$this->includeView();