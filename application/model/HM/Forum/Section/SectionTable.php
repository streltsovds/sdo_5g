<?php

class HM_Forum_Section_SectionTable extends HM_Db_Table
{
    protected $_name = 'forums_sections';
    protected $_primary = 'section_id';
    protected $_sequence = 'S_100_1_FORUM_SECTION';

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        ),
    );
}