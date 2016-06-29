<?
namespace DB\Manager\Result;

class UpdateResult extends BaseResult{
	protected $numAffectedRows;
    protected $id;
    protected $itemData = [];

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
        
    public function setItemData(array $itemData = []){
        $this->itemData = $itemData;
        
        return $this;
    }
    
    public function getItemData(){
        return $this->itemData;
    }

    public function getValue($fieldName, $nullValue = null){
        return isset($this->data[$fieldName]) ? $this->data[$fieldName] : $nullValue ;
    }

    public function getItemValue($fieldName, $nullValue = null){
        return isset($this->itemData[$fieldName]) ? $this->itemData[$fieldName] : $nullValue ;
    }

    public function isNewRecord(){
        return false;
    }
}
