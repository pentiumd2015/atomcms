 <?
use \Entity\Display;

$method = $_REQUEST["method"];
$userID = $this->app("user")->getID();

switch($method){
    case "setDisplaySettings":
        $arResponse = ["result" => 1];
        $entity     = $_REQUEST["entity"];
        $type       = $_REQUEST["type"];
        
        if($entity && class_exists($entity)){
            $obEntity   = new $entity;
            $obDisplay  = new Display($obEntity);
            
            $arData = $_REQUEST["data"];
            
            if(!is_array($arData)){
                $arData = [];
            }
            
            $obDisplay->setDisplayFields($arData, $type, $userID);
            
            $arResponse["hasErrors"] = 0;
        }else{
            $arResponse["hasErrors"] = 1;
            $arResponse["errors"] = "Сущность " . $obEntity->getClass() . " не найдена";
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "getListSettings":
        $arResponse = ["result" => 1];
        $entity     = $_REQUEST["entity"];
        
        if($entity && class_exists($entity)){
            $obEntity   = new $entity;
            $obDisplay  = new Display($obEntity);
            
            $this->setData([
                "obDisplay"         => $obDisplay,
                "arEntityDisplay"   => $obDisplay->getDisplayListFields($userID),
                "obEntity"          => $obEntity
            ]);
            
            $arResponse["content"] = [
                "title"     => "Настройка отображения списка",
                "body"      => $this->getViewContent("list"),
                "buttons"   => [
                    CHtml::button("<i class=\"icon-checkmark\"></i> Применить", [
                        "class" => "btn btn-primary",
                        "onclick" => "saveListSettings();"
                    ]),
                    CHtml::button("Отмена", [
                        "class"     => "btn btn-danger",
                        "data-mode" => "close"
                    ]),
                ]
            ];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "getFilterSettings":
        $arResponse = ["result" => 1];
        $entity     = $_REQUEST["entity"];
        
        if($entity && class_exists($entity)){
            $obEntity   = new $entity;
            $obDisplay  = new Display($obEntity);
            
            $this->setData([
                "obDisplay"         => $obDisplay,
                "arEntityDisplay"   => $obDisplay->getDisplayFilterFields($userID),
                "obEntity"          => $obEntity
            ]);
            
            $arResponse["content"] = [
                "title"     => "Настройка отображения фильтра",
                "body"      => $this->getViewContent("filter"),
                "buttons"   => [
                    CHtml::button("<i class=\"icon-checkmark\"></i> Применить", [
                        "class" => "btn btn-primary",
                        "onclick" => "saveFilterSettings();"
                    ]),
                    CHtml::button("Отмена", [
                        "class"     => "btn btn-danger",
                        "data-mode" => "close"
                    ]),
                ]
            ];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
    case "getDetailSettings":
        $arResponse  = ["result" => 1];
        $entity = $_REQUEST["entity"];
        
        if($entity && class_exists($entity)){
            $obEntity   = new $entity;
            $obDisplay  = new Display($obEntity);
            
            $this->setData([
                "obDisplay"         => $obDisplay,
                "arEntityDisplay"   => $obDisplay->getDisplayDetailFields($userID),
                "obEntity"          => $obEntity
            ]);
            
            $arResponse["content"] = [
                "title"     => "Настройка отображения подробного просмотра",
                "body"      => $this->getViewContent("detail"),
                "buttons"   => [
                    CHtml::button("<i class=\"icon-checkmark\"></i> Применить", [
                        "class" => "btn btn-primary",
                        "onclick" => "saveDetailSettings();"
                    ]),
                    CHtml::button("Отмена", [
                        "class"     => "btn btn-danger",
                        "data-mode" => "close"
                    ]),
                ]
            ];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        break;
}

exit;
?>