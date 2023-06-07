<?php 
class HM_Recruit_Application_ApplicationTable extends HM_Db_Table
{
    protected $_name    = "recruit_application";
    protected $_primary = "recruit_application_id";
    
    // protected $_dependentTables = array();
    
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Department' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            'propertyName'  => 'department' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );
}