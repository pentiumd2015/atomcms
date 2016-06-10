<?
use \Entities\Entity;

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

$arEntities = Entity::findAll(array(
    "pagination"    => $obPagination,
    "order"         => "priority"
));

$this->setData(array(
    "arEntities"    => $arEntities,
    "listElementURL"=> $listElementURL,
    "obPagination"  => $obPagination,
));

$this->includeView("listEntity");
?>