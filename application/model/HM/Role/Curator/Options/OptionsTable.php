<?php

class HM_Role_Curator_Options_OptionsTable extends HM_Db_Table
{
    protected $_name = "curators_options";
    protected $_primary = "user_id";
    //protected $_sequence = 'S_45_1_PEOPLE';
    
    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'users'
        ),
    );

    public function getDefaultOrder()
    {
        return array('curators_options.user_id ASC');
    }
}