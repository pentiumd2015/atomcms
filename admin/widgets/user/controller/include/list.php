<?
use \Entity\Display;

$userID = $this->app("user")->getID();

/*Apply Request Group*/
if($_REQUEST["group"] && $_REQUEST["checkbox_item"]){
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
$obPagination           = new CPagination($_GET["page"], ($_GET["perPage"] ? $_GET["perPage"] : 20));
$arDisplayListFields    = $obDisplay->getDisplayListFields($userID);

$arUsers = $obUser->search([
    "filter"        => $_GET["f"],
    "sort"          => [$_GET["sort"] => $_GET["by"]],
    "pagination"    => $obPagination,
    "select"        => array_keys($arDisplayListFields)
]);

$entityName = $obUser->getEntityName();

$this->setData([
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
?>