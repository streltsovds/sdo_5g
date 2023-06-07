<?php
class HM_Course_Bookmark_BookmarkTable extends HM_Db_Table
{
	protected $_name    = 'organizations_bookmarks';
	protected $_primary = 'bookmark_id';

    protected $_referenceMap = array(
        'CourseItem' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Course_Item_ItemTable',
            'refColumns'    => 'oid',
            'propertyName'  => 'courseItems'
        ),
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Lesson' => array(
            'columns'       => 'lesson_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons'
        ),
        // связька для курс-итемов из ресурса
        'Resource' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'resources'
        ),
    );
}