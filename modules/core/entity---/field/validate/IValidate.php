<?
namespace Entity\Field\Validate;

interface IValidate{
    public function validate($value, $pk, $arData, $obField);
}
?>