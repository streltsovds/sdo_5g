<?php


class HM_Search_FilterState
{
    const QUERY_FILTER = 'query-filter';
    const TAGS_FILTER = 'tags-filter';
    const CLASSIFIERS_FILTER = 'classifiers-filter';

    public static function getValue(string $namespace, string $name)
    {
        $session = new Zend_Session_Namespace('search-filter-'.$namespace);
        return $session->$name;
    }

    public static function setValue(string $namespace, string  $name, $filterValue)
    {
        $session = new Zend_Session_Namespace('search-filter-'.$namespace);
        $session->$name = $filterValue;

        return true;
    }
}