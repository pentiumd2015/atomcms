<?
class CEntity extends \DB\Manager{
    static public function getEntityName(){
        return "tableName";
    }
    
    static public function getBaseFields(){
        return array(
            'id' => array(
                'type' => 'integer',
                'primary' => true,
            ),
            'alias' => array(
                'type' => 'string',
            ),
            'title' => array(
                'type' => 'string',
                'required' => true,
                'title' => "Название",
            ),
            'direction' => array(
                'type' => 'boolean',
                'values' => array(1, 0),
            ),
        );
        
        /*
        'float' => 'Bitrix\Main\Entity\FloatField',
		'string' => 'Bitrix\Main\Entity\StringField',
		'text' => 'Bitrix\Main\Entity\TextField',
		'datetime' => 'Bitrix\Main\Entity\DatetimeField',
		'date' => 'Bitrix\Main\Entity\DateField',
		'integer' => 'Bitrix\Main\Entity\IntegerField',
		'enum' => 'Bitrix\Main\Entity\EnumField',
		'boolean' => 'Bitrix\Main\Entity\BooleanField'
        */
    }
    
    static public function getExtraFields(){
        
    }
}

class CEntityExtraField{
    
}

class CEntityExtraFieldVariant{
    
}

class CEntityExtraFieldValue{
    
}

class CEntityFieldDisplay{
    
}

class CEntityAccess{
    
}

class CEntityFieldAccess{
    
}

class CEntityItem{
    
}
?>