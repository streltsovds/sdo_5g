<?php

class HM_Tc_Application_ApplicationTable extends HM_Db_Table
{
    protected $_name = "tc_applications";
    protected $_primary = "application_id";
    protected $_sequence = "S_106_1_TC_APPLICATIONS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Users' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'DepartmentApplication' => array(
            'columns'       => 'department_application_id',
            'refTableClass' => 'HM_Tc_Session_Department_Application_ApplicationTable',
            'refColumns'    => 'department_application_id',
            'propertyName'  => 'departmentApplications' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subjects'
        ),
        'TcProviders' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns'    => 'provider_id',
            'propertyName'  => 'providers'
        ),
    );

    public function getDefaultOrder()
    {
        return array('tc_applications.application_id ASC');
    }
}