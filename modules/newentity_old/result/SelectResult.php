<?
namespace NewEntity\Result;

class SelectResult extends Result{
    public $statement;
    protected $count = false;
    
    public function __construct(\DB\Statement $obStatement){
        $this->statement = $obStatement;
    }
    
    public function fetchAll($how = NULL){
        return $this->statement->fetchAll($how);
    }
    
    public function fetch($how = NULL){
        return $this->statement->fetch($how);
    }
    
    public function getCount(){
        if($this->count === false){
            $sql = preg_replace("/^(\(*SELECT(.+)FROM)/si", "SELECT COUNT(*) FROM", $this->statement->queryString, 1);
            $this->count = $this->statement->getConnection()->query($sql)->fetchColumn();
        }
        
        return $this->count;
    }
}
?>