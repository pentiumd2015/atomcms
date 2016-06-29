<?
namespace DB\Manager\Result;

class DeleteResult extends BaseResult{
    protected $numAffectedRows;
    protected $id;
    
	public function setNumAffectedRows($numAffectedRows){
		$this->numAffectedRows = $numAffectedRows;
        
        return $this;
	}

	public function getNumAffectedRows(){
		return $this->numAffectedRows;
	}

    public function setId($id){
        $this->id = $id;

        return $this;
    }

    public function getId(){
        return $this->id;
    }
}
