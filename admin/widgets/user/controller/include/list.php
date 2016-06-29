<?
use Entity\Display;
use Data\QueryDataSource;

$addUrl     = $this->getParam("addUrl");
$editUrl    = $this->getParam("editUrl");
$listUrl    = $this->getParam("listUrl");

$request = CAtom::$app->request;

$groupOperation = $request->get("group");
$groupItems     = $request->get("checkbox_item", []);

/*Apply group operations*/
if($groupOperation && count($groupItems)){
    if(($index = array_search(1, $groupItems)) !== false){ //if admin
        unset($groupItems[$index]);
    }

    if(count($groupItems)){
        switch($groupOperation){
            case "activate":
                CUser::updateAll($groupItems, ["active" => 1]);
                break;
            case "deactivate":
                CUser::updateAll($groupItems, ["active" => 0]);
                break;
            case "delete":
                CUser::deleteAll($groupItems);
                break;
        }
    }
}
/*Apply group operations*/

$userId = CAtom::$app->user->getId(); //current user
$user   = new CUser;

$filterData = $request->get("f", []);

$query = $user->search([
    "filter"=> $filterData,
    "sort"  => [$request->get("sort", $user->getPk()) => $request->get("by", "DESC")]
]);

$display = new Display($user);

$dataSource = new QueryDataSource([
    "query"         => $query,
    "pagination"    => [
        "page"      => (int)$request->get("page", 1),
        "perPage"   => (int)$request->get("perPage", 20)
    ]
]);

CBreadcrumbs::add([
    $listUrl => "Список пользователей"
]);

$this->setViewData([
    "dataSource"            => $dataSource,
    "filterData"            => $filterData,
    "listId"                => $user->getEntityName() . "_list",
    "filterId"              => $user->getEntityName() . "_filter",
    "addUrl"                => $addUrl,
    "editUrl"               => $editUrl,
    "displayListFields"     => $display->getDisplayListFields($userId),
    "displayFilterFields"   => $display->getDisplayFilterFields($userId),
]);

$this->includeView("list");