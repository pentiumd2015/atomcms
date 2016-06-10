<?    
    if(!function_exists("app")){
        function app($property = false){
            return \CStorage::get("app")->app($property);
        }
    }
?>