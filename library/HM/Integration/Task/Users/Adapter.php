<?php

class HM_Integration_Task_Users_Adapter extends HM_Integration_Abstract_Adapter implements HM_Integration_Interface_Adapter
{
    protected $_keyExternal = 'mid_external';

    protected $_mapping = array(
        'ID'              => 'mid_external',
        'LastNameRus'     => 'LastName',
        'NameRus'         => 'FirstName',
        'MiddleNameRus'   => 'Patronymic',
        'DateOfBirth'     => 'BirthDate',
        'Gender'          => 'Gender',
        'Education'       => 'Information',
        'SeniorityBegin'  => 'Position',
        'Email'           => 'EMail',
    );

    protected $_externals = array(
    );

    protected $_defaults = array(
        'isAD'    => 1,
    );

    protected function _convertGender($value)
    {
        return ($value == 'Мужской') ? 1 : 2;
    }

    protected function _convertDateOfBirth($value)
    {
        return parent::_convertDate($value);
    }

    protected function _convertSeniorityBegin($value)
    {
        return parent::_convertDate($value);
    }
}