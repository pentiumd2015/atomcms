<?
namespace NewEntity\Result;

class UpdateResult extends Result{
	protected $numAffectedRows;
    protected $id;

	public function setNumAffectedRows($numAffectedRows){
		$this->numAffectedRows = $numAffectedRows;
	}

	public function getNumAffectedRows(){
		return $this->numAffectedRows;
	}

	public function setID($id){
		$this->id = $id;
        
        return $this;
	}
    
	public function getID(){
		return $this->id;
	}
}
