<?php
class HM_User_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        return array(
            0 => 'mid_external',
            1 => 'LastName',
            2 => 'FirstName',
            3 => 'Patronymic',
            4 => 'Login',
            5 => 'EMail',
            6 => 'Password',
            7 => 'isTeacher',
            8 => 'group',
            9 => 'tags',
        );
    }

    public static function getUserFields()
    {
        return array(
            'LastName',
            'FirstName',
            'Patronymic',
            'Login',
            'EMail',
            'Password',
        );
    }

    public static function getOrgstructureFields()
    {
        return array(
            'positionName'
        );
    }

    public static function getOrgName()
    {

    }

    public function getCallbacks()
    {

    }


}