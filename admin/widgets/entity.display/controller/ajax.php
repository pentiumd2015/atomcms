<?
use Entity\Display;
use Helpers\CJson;
use Helpers\CHtml;
use Helpers\CHttpResponse;

$request    = CAtom::$app->request;
$method     = $request->request("method");
$userId     = CAtom::$app->user->getId();
$entity     = $request->request("entity");

switch($method){
    case "setDisplaySettings":
        $response   = ["result" => 1];
        $type       = $request->request("type");

        if($entity && class_exists($entity)){
            $entity   = new $entity;
            $display = new Display($entity);
            $display->setDisplayFields($request->request("data", []), $type, $userId);
            
            $response["hasErrors"] = 0;
        }else{
            $response["hasErrors"] = 1;
            $response["errors"] = "Сущность " . $entity->getClass() . " не найдена";
        }

        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJson::encode($response);
        break;
    case "getListSettings":
        $response = ["result" => 1];
        
        if($entity && class_exists($entity)){
            $entity     = new $entity;
            $display    = new Display($entity);

            $this->setViewData([
                "entity"        => $entity,
                "fields"        => $entity->query()->getFields(),
                "display"       => $display,
                "displayFields" => $display->getDisplayListFields($userId)
            ]);
            
            $response["content"] = [
                "title"     => "Настройка отображения списка",
                "body"      => $this->includeView("list", true),
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
        echo CJson::encode($response);
        break;
    case "getFilterSettings":
        $response = ["result" => 1];
        
        if($entity && class_exists($entity)){
            $entity     = new $entity;
            $display    = new Display($entity);

            $this->setViewData([
                "entity"            => $entity,
                "fields"            => $entity->query()->getFields(),
                "display"           => $display,
                "displayFields"     => $display->getDisplayFilterFields($userId),
            ]);
            
            $response["content"] = [
                "title"     => "Настройка отображения фильтра",
                "body"      => $this->includeView("filter", true),
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
        echo CJson::encode($response);
        break;
    case "getDetailSettings":
        $response = ["result" => 1];
        
        if($entity && class_exists($entity)){
            $entity     = new $entity;
            $display    = new Display($entity);
            
            $this->setViewData([
                "entity"        => $entity,
                "fields"        => $entity->query()->getFields(),
                "display"       => $display,
                "displayFields" => $display->getDisplayDetailFields($userId)
            ]);
            
            $response["content"] = [
                "title"     => "Настройка отображения подробного просмотра",
                "body"      => $this->includeView("detail", true),
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
        echo CJson::encode($response);
        break;
}