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
    switch($groupOperation){
        case "delete":
            $userGroup = CUserGroup::getAllById($groupItems);

            $groupItems = [];

            foreach(CUserGroup::getAllById($groupItems) AS $userGroup){
                if(!CUserGroup::isSystemGroup($userGroup["alias"])){
                    $groupItems[] = $userGroup["id"];
                }
            }

            if(count($groupItems)){
                CUserGroup::deleteAll($groupItems);
            }

            break;
    }
}
/*Apply group operations*/

$userId     = CAtom::$app->user->getId(); //current user
$userGroup  = new CUserGroup;

$filterData = $request->get("f", []);

$query = $userGroup->search([
    "filter"=> $filterData,
    "sort"  => [$request->get("sort", $userGroup->getPk()) => $request->get("by", "DESC")]
]);

$display = new Display;
$display->setManager($userGroup);

$dataSource = new QueryDataSource([
    "query"         => $query,
    "pagination"    => [
        "page"      => (int)$request->get("page", 1),
        "perPage"   => (int)$request->get("perPage", 20)
    ]
]);

CBreadcrumbs::add([
    $listUrl => "Список групп"
]);

$this->setViewData([
    "dataSource"            => $dataSource,
    "filterData"            => $filterData,
    "listId"                => $userGroup->getEntityName() . "_list",
    "filterId"              => $userGroup->getEntityName() . "_filter",
    "addUrl"                => $addUrl,
    "editUrl"               => $editUrl,
    "displayListFields"     => $display->getDisplayListFields($userId),
    "displayFilterFields"   => $display->getDisplayFilterFields($userId),
]);

$this->includeView("list");