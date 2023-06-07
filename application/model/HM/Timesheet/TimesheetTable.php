<?php

class HM_Timesheet_TimesheetTable extends HM_Db_Table
{
    protected $_name = "timesheets";
    protected $_primary = "timesheet_id";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

        'ActionType' => array(
            'columns'       => 'action_type',
            'refTableClass' => 'HM_Classifier_ClassifierTable',
            'refColumns'    => 'classifier_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'action_type' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('timesheets.begin_time ASC');
    }
}