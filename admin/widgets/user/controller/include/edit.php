<?
use \Entity\Display;

$userID = $this->app("user")->getID();
$id     = (int)$this->app("route")->getVarValue("ID");
$obUser = new CUser;
$arFormData = $obUser->getByID($id);

if(!$arFormData){
    CEvent::trigger("404");
}

$formID         = $obUser->getEntityName();
$redirectUrl    = str_replace("{ID}", $id, $editURL);

if($_REQUEST[$formID]){
    $obResult = $obUser->update($id, $_REQUEST[$formID]);

    if(app("request")->isAjax()){
        $arResponse = ["result" => 1];
        
        if($obResult->isSuccess()){
            $arResponse["hasErrors"]    = 0;
            $arResponse["redirectURL"]  = $redirectUrl;
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

CBreadcrumbs::add([
    $listURL    => "Список пользователей",
    $addURL     => $obUser->login
]);

$obDisplay = new Display($obUser);

$this->setData([
    "arDisplayFields"   => $obDisplay->getDisplayDetailFields($userID),
    "formID"            => $formID,
    "listURL"           => $listURL,
    "arFormData"        => $arFormData
]);

$this->includeView("edit");
?>