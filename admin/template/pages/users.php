<?
/*class Some extends \DB\Manager{
    protected static $tableName = "user";

    public function validators(){
        return [
            "login" => [
                new Db\Manager\Validate\Length(4, 8),
                new Db\Manager\Validate\Required(),
                new Db\Manager\Validate\Unique(),
            ]
        ];
    }
}


p(Some::query()->where("id", 1)->add(["login" => "nikita12"]));*/
CWidget::render("user", "index", "index");