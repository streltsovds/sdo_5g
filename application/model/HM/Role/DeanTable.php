<?php

class HM_Role_DeanTable extends HM_Db_Table
{
    protected $_name = "deans";
    protected $_primary = "DID";
    protected $_sequence = "S_21_1_DEANS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subjects'
        ),
        'Responsibility' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Responsibility_ResponsibilityTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'soid', // нужно еще отфильтровать по item_type
        ),
    );

    public function getDefaultOrder()
    {
        return array('deans.DID');
    }
}