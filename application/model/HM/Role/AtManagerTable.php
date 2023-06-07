<?php

class HM_Role_AtManagerTable extends HM_Db_Table
{
    protected $_name = "at_managers";
    protected $_primary = "atmanager_id";
    //protected $_sequence = "S_77_1_ADMINS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'subjects',
        ),
        'Responsibility' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Responsibility_ResponsibilityTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'soid', // нужно еще отфильтровать по item_type
        ),
    );
}