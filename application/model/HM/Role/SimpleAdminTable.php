<?php

class HM_Role_SimpleAdminTable extends HM_Db_Table
{
    protected $_name = "simple_admins";
    protected $_primary = "AID";
    protected $_sequence = "S_77_1_SIMPLE_ADMINS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );

    public function getDefaultOrder()
    {
        return array('simple_admins.AID');
    }
}