<?
use Entity\Display;
use Helpers\CHttpResponse;
use Helpers\CJson;

$addUrl     = $this->getParam("addUrl");
$editUrl    = $this->getParam("editUrl");
$listUrl    = $this->getParam("listUrl");

$userId     = CAtom::$app->user->getId();
$userGroup  = new CUserGroup;
$formId     = $userGroup->getEntityName();

if($_REQUEST[$formId]){
    $result = $userGroup->add($_REQUEST[$formId]);

    if(CAtom::$app->request->isAjax()){
        $response = ["result" => 1];
        
        if($result->isSuccess()){
            $id = $result->getId();
            $response["hasErrors"]    = 0;
            $response["id"]           = $id;
            $response["redirectUrl"]  = str_replace("{ID}", $id, $editUrl);
        }else{
            $response["hasErrors"]    = 1;
            $response["errors"]       = [];
            
            foreach($result->getErrors() AS $fieldError){
                $response["errors"][$fieldError->getFieldName()] = [
                    "code"      => $fieldError->getCode(),
                    "message"   => $fieldError->getMessage()
                ];
            }
        }

        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJson::encode($response);
        exit;
    }
}

CBreadcrumbs::add([
    $listUrl    => "Список групп",
    $addUrl     => "Новая группа"
]);

$display = new Display;
$display->setManager($userGroup);

$this->setViewData([
    "mode"          => "add",
    "displayFields" => $display->getDisplayDetailFields($userId),
    "formId"        => $formId,
    "formData"      => isset($_REQUEST[$formId]) ? $_REQUEST[$formId] : []
]);

$this->includeView("form");