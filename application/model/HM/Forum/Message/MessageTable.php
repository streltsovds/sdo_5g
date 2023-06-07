<?php

class HM_Forum_Message_MessageTable extends HM_Db_Table
{
    protected $_name = 'forums_messages';
    protected $_primary = 'message_id';
    protected $_sequence = 'S_100_1_FORUM_MESSAGE';

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
    );
}