<?
use Entity\Display;
use Helpers\CHttpResponse;
use Helpers\CJson;

$addUrl     = $this->getParam("addUrl");
$editUrl    = $this->getParam("editUrl");
$listUrl    = $this->getParam("listUrl");

$userId = CAtom::$app->user->getId();

$id     = (int)$this->varValues["ID"];
$userGroup = new CUserGroup();
$formData = $userGroup->getByID($id);

if(!$formData){
    CEvent::trigger("404");
}

$formId = $userGroup->getEntityName();

if($_REQUEST[$formId]){
    $result = $userGroup->update($id, $_REQUEST[$formId]);

    if(CAtom::$app->request->isAjax()){
        $response = ["result" => 1];
        
        if($result->isSuccess()){
            $response["hasErrors"]    = 0;
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
    $listUrl                            => "Список групп",
    str_replace("{ID}", $id, $editUrl)  => $formData["title"]
]);

$display = new Display;
$display->setManager($userGroup);

$this->setViewData([
    "mode"          => "edit",
    "displayFields" => $display->getDisplayDetailFields($userId),
    "formId"        => $formId,
    "formData"      => isset($_REQUEST[$formId]) ? $_REQUEST[$formId] : $formData
]);

$this->includeView("form");