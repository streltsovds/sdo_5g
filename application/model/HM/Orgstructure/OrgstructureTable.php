<?php

class HM_Orgstructure_OrgstructureTable extends HM_Db_Table_NestedSet
{
    protected $_name = "structure_of_organ";
    protected $_primary = "soid";
    protected $_left = 'lft';
    protected $_right = 'rgt';
    protected $_level = 'level';
    protected $_sequence = "S_61_1_STRUCTURE_OF_ORGAN";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Employee' => array( // ВНИМАНИЕ! Это то же самое, что 'User', но 'User' не работает в MSSQL!
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'employee'
        ),
        'Descendant' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'owner_soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'descendants'
        ),
        'Parent' => array(
            'columns'       => 'owner_soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'parent'
        ),
        'Sibling' => array( // ВНИМАНИЕ! не совсем оно работает.. например условия вида 'Sibling.attr = ?'  в fetchAllDependenceJoinInner
            'columns'       => 'owner_soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'owner_soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'siblings'
        ),
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ),
        'SessionUser' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'session_users'
        ),
        'SessionEvent' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'session_events'
        ),
        'Vacancy' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'vacancy'
        ),
        'Newcomer' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'position_id',
            'propertyName'  => 'reserve'
        ),
        'Recruiter' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'recruiter'
        ),
        'Responsibility' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Responsibility_ResponsibilityTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'responsibility' // нужно еще отфильтровать по type
        ),
        'TcSessionDepartment' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Tc_Session_Department_DepartmentTable',
            'refColumns'    => 'department_id',
            'propertyName'  => 'tc_departments' // нужно еще отфильтровать по type
        ),
        'RecruitApplication' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Recruit_Application_ApplicationTable',
            'refColumns'    => 'soid',
            'propertyName'  => 'recruitApplications' // нужно еще отфильтровать по type
        ),
        'StaffUnit' => array(
            'columns'       => 'staff_unit_id',
            'refTableClass' => 'HM_StaffUnit_StaffUnitTable',
            'refColumns'    => 'staff_unit_id',
            'propertyName'  => 'staffUnit' // нужно еще отфильтровать по type
        ),
    );

    public function getDefaultOrder()
    {
        return array('structure_of_organ.soid ASC');
    }
}