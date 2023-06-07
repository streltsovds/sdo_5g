<?php
class HM_Programm_ProgrammTable extends HM_Db_Table
{
	protected $_name = "programm";
    protected $_primary = "programm_id";
    protected $_sequence = "S_45_1_PROGRAMM";

    protected $_referenceMap = array(
        'Process' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Process_ProcessTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'process'
        ),
        'Event' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'events'
        ),
        // @todo: устранить дублирование
        'ProgrammEvents' => array(
            'columns' => 'programm_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns' => 'programm_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'programm_events'
         ),
        'Subject' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject'
        ),
        'User' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_User_UserTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'users'
        ),
        'EventUser' => array(
            'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_Event_User_UserTable',
            'refColumns'    => 'programm_id',
            'propertyName'  => 'eventUsers'
        ),
        'Profile' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'profile_id',
            'propertyName'  => 'profile'
        ),
        'Category' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns'    => 'category_id',
            'propertyName'  => 'category'
        ),
        'Vacancy' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_Recruit_Vacancy_VacancyTable',
            'refColumns'    => 'vacancy_id',
            'propertyName'  => 'vacancy'
        ),
        'Newcomer' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns'    => 'newcomer_id',
            'propertyName'  => 'newcomer'
        ),
        'Reserve' => array(
            'columns'       => 'item_id', // нужно еще отфильтровать по type
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'reserve_id',
            'propertyName'  => 'reserve',
        ),
    );
}