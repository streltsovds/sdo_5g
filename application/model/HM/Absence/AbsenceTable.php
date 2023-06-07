<?php
class HM_Absence_AbsenceTable extends HM_Db_Table
{
	protected $_name = "absence";
	protected $_primary = "absence_id";
    protected $_sequence = 'S_100_1_ABSENCE';

    protected $_referenceMap = array(
       'User' => array(
           'columns'       => 'user_id',
           'refTableClass' => 'HM_User_UserTable',
           'refColumns'    => 'MID',
           'propertyName'  => 'user'
       ),
    );    
}