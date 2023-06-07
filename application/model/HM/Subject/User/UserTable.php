<?php

class HM_Subject_User_UserTable extends HM_Db_Table
{
    protected $_name = "subjects_users";
    protected $_primary = array("subject_id", "user_id");

    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subject'
        ),
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'user'
        ),
        'Claimant' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_ClaimantTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'claimant' // нужно еще отфильтровать по Subject
        ),
        'Student' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'student' // нужно еще отфильтровать по Subject
        ),
        'Graduated' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_GraduatedTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'graduated' // нужно еще отфильтровать по Subject
        ),
        'Teacher' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_Role_TeacherTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'graduated' // нужно еще отфильтровать по Subject
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_courses.subject_id ASC');
    }
}