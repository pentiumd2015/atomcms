<?
namespace DB\Manager\Result;

class AddResult extends BaseResult{
	protected $id;

	public function setId($id){
		$this->id = $id;
        
        return $this;
	}
    
	public function getId(){
		return $this->id;
	}

	public function getValue($fieldName, $nullValue = null){
		return isset($this->data[$fieldName]) ? $this->data[$fieldName] : $nullValue ;
	}

	public function isNewRecord(){
		return true;
	}
}
