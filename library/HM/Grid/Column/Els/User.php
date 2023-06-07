<?php

class HM_Grid_Column_Els_User extends HM_Grid_ReplacementColumn
{
    const TYPE = 'els.user';

    protected $_table = 'People';

    protected function _getTitleExpression()
    {
        $parts = array(
            'LastName',
            'FirstName',
            'Patronymic'
        );

        $a = $this->_tableAlias.'.';

        $result = $a.$parts[0];

        for ($i = 1; $i < 3; $i++) {
            $result = 'CONCAT('.$result.', \' \')';
            $result = 'CONCAT('.$result.','.$a.$parts[$i].')';
        }

        return new Zend_Db_Expr($result);

    }

    protected function _getColumnCallback()
    {
        $callBack = new HM_Grid_ColumnCallback_Els_UserCardLink();

        return $callBack->getCallback(
            '{{'.$this->getFieldName().'}}',
            '{{'.$this->_columnAlias.'}}'
        );
    }

}