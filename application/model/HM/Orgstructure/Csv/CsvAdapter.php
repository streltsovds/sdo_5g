<?php
class HM_Orgstructure_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    protected $_skipLines = 1;
/*
 * mapper parameter array (field,callback,format(string(default),array,integer))
 * 
 */
    public function getMappingArray()
    {
        return array(
            0 => 'owner_soid_external', // ВНИМАНИЕ!!! на самом деле это owner_soid_external; он заменяется на owner_soid непосредственно перед inser/update 
            1 => 'soid_external',
            2 => 'name',
            3 => 'is_manager',
            4 => 'mid_external',
            5 => array('field'=>'fio','callback'=>'convertName','format'=>'array'),
            6 => array('field'=>'Login','callback'=>'convertLogin'),
            7 => 'EMail',
        );
    }

    protected function convertLogin($arg)
    {
        return strtolower($arg);
    }

    protected function convertName($arg)
    {
        $row = array();
        list($row['LastName'],$row['FirstName'],$row['Patronymic']) = explode(' ',$arg);
        return $row;
    }

    protected function convertDate($arg)
    {
        if($arg != ''){
           return $date = new HM_Date($arg);
        }
        return '';
    }
    
    public static function getUserFields()
    {
        return array(
                'mid_external',
                'LastName',
                'FirstName',
                'Patronymic',
                'Login',
                'EMail',
        );
    }
    
    public static function getOrgFields()
    {
        return array(
                'owner_soid_external',
                'soid_external',
                'mid_external',
                'name',
                'is_manager',
                'type',
        );
    }

}