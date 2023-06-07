<?php
class HM_StudyGroup_Users_UsersTable extends HM_Db_Table
{
	protected $_name = "study_groups_users";
	protected $_primary = [
        'group_id',
	    'user_id'
    ];

    protected $_dependentTables = [];

    protected $_referenceMap = [
        'User' => [
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ],
        'StudyGroup' => [
            'columns'       => 'group_id',
            'refTableClass' => 'HM_StudyGroup_StudyGroupTable',
            'refColumns'    => 'group_id',
            'propertyName'  => 'groups' // имя свойства текущей модели куда будут записываться модели зависимости
        ]
    ];
}