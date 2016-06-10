<?
if(!CModule::load("core.entity")){
    echo 'Bad';
}

$r = CUser::builder()->select("active", "login AS t", "some AS s", "group_title")
//->alias("s")
                    // ->whereIn('group_id', [1,4])
                //     ->where('group_title', 'LIKE', "test")
                     
                    // ->whereIn("some", [56, 78])
                     //->where("some", 5)
                    // ->groupBy(["id", "group_title"])
                   //  ->orderBy(["some" => "ASC", "login" => "DESC", "group_title" => "ASC", "group_id" => "DESC"])/**/
                   //  ->getDbBuilder()
                   //  ->fetchAll();
                   
                   ->fetchAll();

p($r);

exit;
?>