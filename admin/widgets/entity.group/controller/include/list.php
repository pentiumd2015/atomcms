<?
use \Entities\EntityGroup;

//$userID = $this->app("user")->user_id;

/*PerPage*/
$arPerPage = array(
    1   => "1",
    50  => "50",
    100 => "100",
    -1  => "Все"
);

$perPage = 50;

if(isset($_REQUEST["perPage"]) && isset($arPerPage[$_REQUEST["perPage"]])){
    $perPage = $_REQUEST["perPage"];
}

$obPagination = new \Helpers\Pagination($_REQUEST["page"], $perPage);
/*PerPage*/

$arEntityGroups = EntityGroup::findAll(array(
    "pagination"    => $obPagination,
    "order"         => "title"
));

$this->setData(array(
    "arEntityGroups"=> $arEntityGroups,
    "addURL"        => $addURL,
    "editURL"       => $editURL,
    "listURL"       => $listURL,
    "obPagination"  => $obPagination,
));

$this->includeView("list");
?>