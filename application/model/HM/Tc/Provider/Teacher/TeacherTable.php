<?php

class HM_Tc_Provider_Teacher_TeacherTable extends HM_Db_Table
{
    protected $_name = "tc_provider_teachers";
    protected $_primary = "teacher_id";
    protected $_sequence = "S_106_1_TC_PROVIDER_TEACHERS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Provider' => array(
            'columns'       => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns'    => 'provider_id',
            'propertyName'  => 'providers' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Teacher' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'TeacherSubjects' => array(
            'columns'       => 'teacher_id',
            'refTableClass' => 'HM_Tc_Provider_Teacher_Subject_SubjectTable',
            'refColumns'    => 'teacher_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'teachers2subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('tc_provider_teachers.teacher_id ASC');
    }
}