<?php

use HM_Grid_ColumnCallback_Els_UserCardLink as UserCardLink;

class HM_Grid_ColumnCallback_Els_UserList extends HM_Grid_ColumnCallback_AbstractListOfCardLinks
{
    protected $_defaultServiceName = 'User';

    protected function _createCardLinkCallback()
    {
        return new UserCardLink();
    }

    protected function _getTitle($item)
    {
        return $item->getName();
    }

    protected function _getFilterExpression($options)
    {
        $tableAlias = $options['tableAlias'];
        $search     = $options['search'];
        $service    = $this->getService();

        $LastName   = $tableAlias.'.LastName';
        $FirstName  = $tableAlias.'.FirstName';
        $Patronymic = $tableAlias.'.Patronymic';

        $haystack = "CONCAT(CONCAT(CONCAT(CONCAT($LastName, ' '), $FirstName), ' '), $Patronymic)";

        return $service->quoteInto('LOWER('.$haystack.') LIKE ?', '%'.$search.'%');
    }

}