<?
class CAdminRouter extends CRouter{
    protected $arRoutes = array();
    
    public function __construct($arRoutes = array()){
        $this->arRoutes = $this->prepare($arRoutes);
    }
    
    public function addRoutes($arRoutes = array()){
        return $this->arRoutes = array_merge($this->arRoutes, $this->prepare($arRoutes));
    }
    
    public function setRoutes($arRoutes = array()){
        return $this->arRoutes = $this->prepare($arRoutes);
    }
    
    public function getRoutes(){
        return $this->arRoutes;
    }
    
    public function prepare($arRoutes){
        foreach($arRoutes AS $path => &$arRouteItem){
            $arRouteItem["path"] = $path;
        }
        
        unset($arRouteItem);
        
        return $arRoutes;
    }
}
?>