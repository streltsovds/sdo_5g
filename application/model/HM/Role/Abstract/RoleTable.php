<?php

class HM_Role_Abstract_RoleTable extends HM_Db_Table
{
    protected $_name = "roles_source";
    protected $_primary = "user_id";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );
}