<?
namespace DB\Manager\Validate;

interface IValidate{
    public function validate($value, $column, $result, $manager);
}