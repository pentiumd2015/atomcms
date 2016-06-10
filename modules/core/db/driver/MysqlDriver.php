<?
namespace DB\Driver;

class MysqlDriver extends Driver{
    protected $obResult;

    public function freeResult(){
        $this->obResult = NULL;
    }
    
    public function query($sql, $arParams = array()){
        $this->obResult = $this->prepare($sql);
        $this->obResult->execute($arParams);
        
        return $this->obResult;
    }
    
    public function parameter($value){
		return $this->isExpression($value) ? $value->getValue() : "?";
	}
    
    public function isExpression($value){
		return $value instanceof \DB\Expr;
	}
    
    public function insert($tableName, $arData){
        $sql = "INSERT INTO " . $this->quoteTable($tableName);
        
		if(!is_array(reset($arData))){
			$arData = array($arData);
		}
        
        $columns = "(" . implode(", ", array_map(array($this, "quoteColumn"), array_keys(reset($arData)))) . ")";
        
        $sql.= " " . $columns;
        
        //column values
        $params = implode(", ", array_map(array($this, "parameter"), reset($arData)));
        
        //multi values
        $values = implode(", ", array_fill(0, count($arData), "(" . $params . ")"));
        
        $sql.= " VALUES " . $values;
        
        $arParams = array();
        
		foreach($arData AS $arRecords){
			foreach($arRecords AS $value){
                if(!$this->isExpression($value)){
                    $arParams[] = $value;
                }
			}
		}

        $this->query($sql, $arParams);
        
        return $this->lastInsertId();
    }
    
    public function update($tableName, $arData, $whereSql = "", $arParams = array()){
        $sql = "UPDATE " . $this->quoteTable($tableName);

        $arColumns = array();
        
		foreach($arData AS $key => $value){
			$arColumns[] = $this->quoteColumn($key) . " = " . $this->parameter($value);
		}
        
		$sql.= " \nSET " . implode(", ", $arColumns);
        
        if(strlen($whereSql)){
            $sql.= " \nWHERE " . $whereSql;
        }
        
        $arDataParams = array();
        
		foreach($arData AS $value){
			if(!$this->isExpression($value)){
                $arDataParams[] = $value;
            }
		}
        
        $arParams = array_values(array_merge($arDataParams, $arParams));

        $res = $this->query($sql, $arParams);

        return $res->rowCount();
    }

    public function getVersion(){
        if(preg_match('/(\d+)\.(\d+)\.(\d+)/', $this->getAttribute(self::ATTR_SERVER_VERSION), $match)){
            return $match[1] . '.' . $match[2] . '.' . $match[3];
        }else{
            return false;
        }
	}
}
?>