<?

class CAtom{
    public static $app;
    
    public static function app($appService = null){
        if(static::$app !== null){
            return $appService ? static::$app->{$appService} : static::$app ;
        }

        return null;
    }
}