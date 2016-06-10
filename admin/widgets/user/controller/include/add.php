<?
use \Entity\Display;

$userID = $this->app("user")->getID();

$obUser = new CUser;
$formID = $obUser->getEntityName();

if($_REQUEST[$formID]){
    $obResult = $obUser->add($_REQUEST[$formID]);

    if(CHttpRequest::isAjax()){
        $arResponse = ["result" => 1];
        
        if($obResult->isSuccess()){
            $id = $obResult->getID();
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $id;
            $arResponse["redirectURL"]  = str_replace("{ID}", $id, $editURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = [];
            
            foreach($obResult->getErrors() AS $obFieldError){
                $arResponse["errors"][$obFieldError->getFieldName()] = [
                    "code"      => $obFieldError->getCode(),
                    "message"   => $obFieldError->getMessage()
                ];
            }
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }
}

$arFormData = isset($_REQUEST[$formID]) ? $_REQUEST[$formID] : [];

CBreadcrumbs::add([
    $listURL    => "Список пользователей",
    $addURL     => "Новый пользователь"
]);

$obDisplay = new Display($obUser);

$this->setData([
    "arDisplayFields"   => $obDisplay->getDisplayDetailFields($userID),
    "formID"            => $formID,
    "listURL"           => $listURL,
    "addURL"            => $addURL,
    "arFormData"        => $arFormData
]);

$this->includeView("add");
?>