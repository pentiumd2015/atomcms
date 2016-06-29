<?
$route = CAtom::$app->route;

$addPattern     = "add";
$editPattern    = "{ID}";

$this->varValues  = [];
$this->mode       = null;

if($route->query){
    $routes = [
        "add"   => ["pattern" => $addPattern],
        "edit"  => [
            "pattern"   => $editPattern, 
            "varParams" => [
                "ID" => "^\d+$"
            ]
        ]
    ];
    
    if(($values = $route->getMatch($routes)) !== false){
        $this->mode         = $values["mode"];
        $this->varValues    = $values["varValues"];
    }
}else{
    $this->mode = "list";
}

$filePath = __DIR__ . "/include/";

if($this->mode && is_file($filePath . $this->mode . ".php")){
    $this->setParam("listUrl", $route->path);
    $this->setParam("addUrl", $route->path . $addPattern);
    $this->setParam("editUrl", $route->path . $editPattern);

    include($filePath . $this->mode . ".php");
}else{
    CEvent::trigger("404");
}