<?
namespace Data;

abstract class BaseDataSource{
    abstract public function getData();
    abstract public function getPagination();
}