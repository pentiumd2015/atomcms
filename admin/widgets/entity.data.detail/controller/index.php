<?
use Entity\Manager;
use Helpers\CJson;
use Helpers\CHtml;

$params = $this->getParams();

$params["entity"] = isset($params["entity"]) ? $params["entity"] : null ;

if($params["entity"] && class_exists($params["entity"])){
    $params["entity"] = new $params["entity"];
}

if(!$params["entity"] instanceof Manager){
    throw new CException("Entity is required for widget entity.data.detail");
}

$params["settingsUrl"]      = isset($params["settingsUrl"]) ? $params["settingsUrl"] : null;
$params["formId"]           = isset($params["formId"]) ? $params["formId"] : "detail";
$params["data"]             = isset($params["data"]) && is_array($params["data"]) ? $params["data"] : [] ;
$params["formData"]         = isset($params["formData"]) && is_array($params["formData"]) ? $params["formData"] : [] ;
$params["attributes"]       = isset($params["attributes"]) && is_array($params["attributes"]) ? $params["attributes"] : ["class" => "form-horizontal"] ;
$params["buttons"]          = isset($params["buttons"]) ? $params["buttons"] : CHtml::button("Сохранить", [
    "type"  => "submit",
    "class" => "btn btn-primary"
]);

$params["url"]          = isset($params["url"]) ? $params["url"] : CAtom::$app->route->url;
$params["tabs"]         = isset($params["tabs"]) && is_array($params["tabs"]) ? $params["tabs"] : [] ;

$params["attributes"]   = array_merge($params["attributes"], [
    "id"        => $params["formId"],
    "method"    => "POST",
    "action"    => $params["url"]
]);

$params["rendererParams"] = [
    "requestName" => $params["formId"]
];

$params["jsonParams"] = CJson::encode($params);

$fields = $params["entity"]->query()->getFields();

$tabs = [];

foreach($params["tabs"] AS $tabIndex => $tab){
    $tabFields = [];
    if(isset($tab["fields"]) && is_array($tab["fields"])){
        foreach($tab["fields"] AS $fieldName => $field){
            if(is_numeric($fieldName)){
                if(is_string($field)){
                    $fieldName = $field;

                    if(isset($fields[$fieldName]) && $fields[$fieldName]->filterable){
                        $fieldRenderer = $fields[$fieldName]->getRenderer();

                        $tabFields[$fieldName] = ["renderer" => [$fieldRenderer, "renderDetail"]];
                    }
                }
            }else{
                if(isset($field["renderer"]) && is_callable($field["renderer"])){
                    $tabFields[$fieldName] = $field;
                }else{
                    throw new CException("Field Renderer not set in 'renderer' key of fields array");
                }
            }
        }
    }else{
        $params["tabs"][$tabIndex]["fields"] = [];
    }
    
    $tab["fields"] = $tabFields;
    
    $tabs[] = $tab;
}

$params["tabs"] = $tabs;

$this->view->addJs(BASE_URL . $this->path . "js/script.js");

$this->setViewData($params);

$this->includeView();