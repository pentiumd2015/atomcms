<?
use \Entity\Display;

$userID = $this->app("user")->getID();

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        switch($_REQUEST["group"]){
            case "delete":
                CUserGroup::deleteAll($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/

$obUserGroup = new CUserGroup;

$arDisplayListFields = Display::getDisplayListFields($obUserGroup, $userID);

$obPagination = new CPagination($_REQUEST["page"], ($_REQUEST["perPage"] ? $_REQUEST["perPage"] : 20));

$arUserGroups = $obUserGroup->search(array(
    "filter"        => $_REQUEST["f"],
    "sort"          => array($_REQUEST["sort"] => $_REQUEST["by"]),
    "pagination"    => $obPagination,
    "select"        => array_merge(array("*"), array_keys($arDisplayListFields)) //нам нужно получить все базовые поля. нужен алиас для распознавания системных групп
));

$entityName = CUserGroup::getTableName();

$this->setData(array(
    "listID"                => $entityName . "_list",
    "filterID"              => $entityName . "_filter",
    "arUserGroups"          => $arUserGroups,
    "addURL"                => $addURL,
    "editURL"               => $editURL,
    "obPagination"          => $obPagination,
    "arDisplayListFields"   => $arDisplayListFields,
    "arDisplayFilterFields" => Display::getDisplayFilterFields($obUserGroup, $userID),
));

$this->includeView("list");
?>