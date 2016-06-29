<?

class CBreadcrumbs{
    static public function add(array $data = []){
        $view = CAtom::$app->view;
        $breadcrumbs = $view->getDynamicData("BREADCRUMBS");
        
        if(!isset($breadcrumbs["items"])){
            $breadcrumbs["items"] = [];
        }

        $breadcrumbs["items"] = array_merge($breadcrumbs["items"], $data);
        
        $view->setDynamicData("BREADCRUMBS", $breadcrumbs);
    }
    
    static public function show($callback){
        echo CAtom::$app->view->addDynamic("BREADCRUMBS", $callback);
    }
}