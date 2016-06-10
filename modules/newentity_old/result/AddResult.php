<?
namespace NewEntity\Result;

class AddResult extends Result{
	protected $id;

	public function setID($id){
		$this->id = $id;
        
        return $this;
	}
    
	public function getID(){
		return $this->id;
	}
}
