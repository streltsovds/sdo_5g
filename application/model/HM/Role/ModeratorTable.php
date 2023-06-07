<?php

class HM_Role_ModeratorTable extends HM_Db_Table
{
    protected $_name = "moderators";
    protected $_primary = "moderator_id";
    protected $_sequence = "S_63_3_MODERATORS";

    protected $_referenceMap = array(
    		'User' => array(
    				'columns'       => 'user_id',
    				'refTableClass' => 'HM_User_UserTable',
    				'refColumns'    => 'MID',
    				'propertyName'  => 'users'
    		),
    		'Project' => array(
    				'columns'       => 'project_id',
    				'refTableClass' => 'HM_Project_ProjectTable',
    				'refColumns'    => 'projid',
    				'propertyName'  => 'projects'
    		)
    
    );
    
    public function getDefaultOrder()
    {
        return array('moderators.moderator_id');
    }
}