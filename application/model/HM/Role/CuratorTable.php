<?php

class HM_Role_CuratorTable extends HM_Db_Table
{
    protected $_name = "curators";
    protected $_primary = "curator_id";
    protected $_sequence = "S_21_1_CURATORS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        ),
        'Project' => array(
            'columns'       => 'project_id',
            'refTableClass' => 'HM_Project_ProjectTable',
            'refColumns'    => 'projid',
            'propertyName'  => 'projects'
        ),
    );

    public function getDefaultOrder()
    {
        return array('curators.curator_id');
    }
}