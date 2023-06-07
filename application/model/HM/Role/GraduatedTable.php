<?php

class HM_Role_GraduatedTable extends HM_Db_Table
{
    protected $_name = "graduated";
    protected $_primary = "SID";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName' =>  'user'
        ),
        'Subject' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject'
        ),
        'SubjectUser' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Subject_User_UserTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'subjectUser'
        ),
    );

    public function getDefaultOrder()
    {
        return array('graduated.SID');
    }
}