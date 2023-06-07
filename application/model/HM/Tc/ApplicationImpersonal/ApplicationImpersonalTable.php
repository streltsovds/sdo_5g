<?php

class HM_Tc_ApplicationImpersonal_ApplicationImpersonalTable extends HM_Db_Table
{
    protected $_name = "tc_applications_impersonal";
    protected $_primary = "application_impersonal_id";
    protected $_sequence = "S_106_1_TC_APPLICATIONS_IMPERSONAL";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'DepartmentApplication' => array(
            'columns'       => 'department_application_id',
            'refTableClass' => 'HM_Tc_Session_Department_Application_ApplicationTable',
            'refColumns'    => 'department_application_id',
            'propertyName'  => 'departmentApplication' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subjects'
        ),
    );

    public function getDefaultOrder()
    {
        return array('tc_applications_impersonal.application_impersonal_id ASC');
    }
}