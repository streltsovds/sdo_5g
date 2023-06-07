<?php
class HM_At_Session_User_CriterionValue_CriterionValueTable extends HM_Db_Table
{
    protected $_name = "at_session_user_criterion_values";
    //protected $_primary = "user_id";
    //protected $_sequence = "????"; // @todo

    protected $_referenceMap = array(
        'SessionUser' => array(
            'columns'       => 'session_user_id',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns'    => 'session_user_id',
            'propertyName'  => 'session_user'
        ), 
//         'Session' => array(
//             'columns'       => 'session_id',
//             'refTableClass' => 'HM_At_Session_SessionTable',
//             'refColumns'    => 'session_id',
//             'propertyName'  => 'session'
//         ), 
//         'Criterion' => array(
//             'columns'       => 'criterion_id',
//             'refTableClass' => 'HM_At_Evaluation_Criterion_CriterionTable',
//             'refColumns'    => 'criterion_id',
//             'propertyName'  => 'criterion'
//         ), 
//         'ScaleValue' => array(
//             'columns'       => 'value_id',
//             'refTableClass' => 'HM_Scale_ScaleTable',
//             'refColumns'    => 'value_id',
//             'propertyName'  => 'scaleValue'
//         ), 
    );
}