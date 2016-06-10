<?
namespace NewEntity\Result;

class DeleteResult extends Result{
    protected $id;
    
    public function setID($id){
        $this->id = $id;
        
        return $this;
    }
    
    public function getID(){
        return $thid->id;
    }
}
