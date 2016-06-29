<?
use Data\QueryDataSource;
use Helpers\CJson;

$params = $this->getParams();

if(isset($params["dataSource"]) && $params["dataSource"] instanceof QueryDataSource){
    $dataSource             = $params["dataSource"];
    $params["data"]         = $dataSource->getData();
    $params["pagination"]   = $dataSource->getPagination();
    $params["query"]        = $dataSource->getQuery();
}else{
    throw new CException("dataSource must be \Data\QueryDataSource");
}

$params["headPanel"]                = isset($params["headPanel"]) && is_array($params["headPanel"]) ? $params["headPanel"] : [] ;
$params["controls"]                 = isset($params["controls"]) ? $params["controls"] : null ;
$params["listId"]                   = isset($params["listId"]) ? $params["listId"] : "list";
$params["fields"]                   = isset($params["fields"]) && is_array($params["fields"]) ? $params["fields"] : [] ;
$params["options"]                  = isset($params["options"]) && is_array($params["options"]) ? $params["options"] : [] ;
$params["onRowOptions"]             = isset($params["onRowOptions"]) && is_callable($params["onRowOptions"]) ? $params["onRowOptions"] : null ;
$params["onCellOptions"]            = isset($params["onCellOptions"]) && is_callable($params["onCellOptions"]) ? $params["onCellOptions"] : null ;
$params["tableAttributes"]          = ["id" => $params["listId"]] + (isset($params["tableAttributes"]) && is_array($params["tableAttributes"]) ? $params["tableAttributes"] : ["class" => "table table-striped table-bordered table-hover"]) ;
$params["headAttributes"]           = ["id" => $params["listId"] . "_thead"] + (isset($params["headAttributes"]) && is_array($params["headAttributes"]) ? $params["headAttributes"] : []) ;
$params["bodyAttributes"]           = ["id" => $params["listId"] . "_tbody"] + (isset($params["bodyAttributes"]) && is_array($params["bodyAttributes"]) ? $params["bodyAttributes"] : []) ;
$params["groupOperationKey"]        = isset($params["groupOperationKey"]) ? $params["groupOperationKey"] : "group" ;
$params["sortKey"]                  = isset($params["sortKey"]) ? $params["sortKey"] : "sort" ;
$params["sortByKey"]                = isset($params["sortByKey"]) ? $params["sortByKey"] : "by" ;
$params["pageKey"]                  = isset($params["pageKey"]) ? $params["pageKey"] : "page" ;
$params["perPageKey"]               = isset($params["perPageKey"]) ? $params["perPageKey"] : "perPage" ;
$params["baseURL"]                  = isset($params["baseURL"]) ? $params["baseURL"] : CAtom::$app->route->url ;
$params["onApplyGroupOperation"]    = isset($params["onApplyGroupOperation"]) ? $params["onApplyGroupOperation"] : "" ;
$params["onChangeGroupOperation"]   = isset($params["onChangeGroupOperation"]) ? $params["onChangeGroupOperation"] : "" ;
$params["onApplyBefore"]            = isset($params["onApplyBefore"]) ? $params["onApplyBefore"] : "onApplyListBefore" ;
$params["onApplyAfter"]             = isset($params["onApplyAfter"]) ? $params["onApplyAfter"] : "onApplyListAfter" ;
$params["groupOperationsContent"]   = isset($params["groupOperationsContent"]) ? $params["groupOperationsContent"] : null ;
$params["perPageList"]              = isset($params["perPageList"]) && is_array($params["perPageList"]) && count($params["perPageList"]) ? $params["perPageList"] : [
    20  => "20",
    50  => "50",
    100 => "100",
    -1  => "Все"
];

$params["groupOperations"] = isset($params["groupOperations"]) && is_array($params["groupOperations"]) ? $params["groupOperations"] : [];

$params["primaryKey"] = $params["query"]->getManager()->getPk();
$params["jsonParams"] = CJson::encode($params);

$fields = $params["query"]->getFields();
$params["query"]->select($params["columns"]);

$tmpFields = [];

foreach($params["fields"] AS $fieldName => $field){
    if(is_numeric($fieldName)){
        if(is_string($field)){
            $fieldName = $field;

            if(isset($fields[$fieldName]) && $fields[$fieldName]->filterable){
                $fieldRenderer = $fields[$fieldName]->getRenderer();

                $tmpFields[$fieldName] = ["renderer" => [$fieldRenderer, "renderList"]];
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

$this->view->addJs(BASE_URL . $this->path . "js/colResizable-1.5.min.js");
$this->view->addJs(BASE_URL . $this->path . "js/script.js");
$this->view->addCss(BASE_URL . $this->path . "css/style.css");

$this->setViewData($params);

$this->includeView();