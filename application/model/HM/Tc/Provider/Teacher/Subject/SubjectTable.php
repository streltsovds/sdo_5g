<?php

class HM_Tc_Provider_Teacher_Subject_SubjectTable extends HM_Db_Table
{
    protected $_name = "tc_provider_teachers2subjects";
    protected $_primary = array('teacher_id', 'subject_id');
    protected $_sequence = "S_106_1_TC_PROVIDER_TEACHERS2SUBJECTS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'TcTeacher' => array(
            'columns'       => 'teacher_id',
            'refTableClass' => 'HM_Tc_Provider_Teacher_TeacherTable',
            'refColumns'    => 'teacher_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'tcTeachers' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

}