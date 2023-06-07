<?php
class HM_At_Profile_Function_FunctionTable extends HM_Db_Table
{
    protected $_name = "at_profile_function";
    protected $_primary = array('profile_function_id');
    //protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'Profile' => array(
            'columns'       => 'profile_id',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ), 
    );
}