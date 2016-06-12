<?
use Entity\Display;
use Helpers\CPagination;

$userID = CAtom::$app->user->getID();

/*Apply Request Group*/
if(isset($_REQUEST["group"]) && $_REQUEST["checkbox_item"]){
    $arGroupItems = $_REQUEST["checkbox_item"];

    if(is_array($arGroupItems)){
        switch($_REQUEST["group"]){
            case "activate":
                CUser::updateAll($arGroupItems, ["active" => 1]);
                break;
            case "deactivate":
                CUser::updateAll($arGroupItems, ["active" => 0]);
                break;
            case "delete":
                CUser::deleteAll($arGroupItems);
                break;
        }
    }
}
/*Apply Request Group*/
$obUser                 = new CUser;
$obDisplay              = new Display($obUser);
$obPagination           = new CPagination(isset($_GET["page"]) ? (int)$_GET["page"] : 1, (isset($_GET["perPage"]) ? (int)$_GET["perPage"] : 20));

$arDisplayListFields    = $obDisplay->getDisplayListFields($userID);

$arUsers = $obUser->search([
    "filter"        => isset($_GET["f"]) ? $_GET["f"] : [],
    "sort"          => [isset($_GET["sort"]) ? $_GET["sort"] : "id" => isset($_GET["by"]) ? $_GET["by"] : "ASC"],
    "pagination"    => $obPagination,
    "select"        => array_keys($arDisplayListFields)
]);

$entityName = $obUser->getEntityName();

$this->setViewData([
    "listID"                    => $entityName . "_list",
    "filterID"                  => $entityName . "_filter",
    "arUsers"                   => $arUsers,
    "addURL"                    => $addURL,
    "editURL"                   => $editURL,
    "obPagination"              => $obPagination,
    "arDisplayListFields"       => $arDisplayListFields,
    "arDisplayFilterFields"     => $obDisplay->getDisplayFilterFields($userID),
]);

$this->includeView("list");