<?php

class HM_Resource_ResourceTable extends HM_Db_Table
{
    protected $_name = "resources";
    protected $_primary = "resource_id";
    protected $_sequence = "S_100_1_RESOURCES";
/*
    protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );
*/

    protected $_referenceMap = array(
        'ProjectAssign' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Project_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'projects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'SubjectAssign' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Subject_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Lesson' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'material_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'lessons'
        ),
        'Revision' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Resource_Revision_RevisionTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'revisions'
        ),
        'ParentRevision' => array(
            'columns'       => 'parent_revision_id',
            'refTableClass' => 'HM_Resource_Revision_RevisionTable',
            'refColumns'    => 'revision_id',
            'propertyName'  => 'parentRevision'
        ),
        'ParentResource' => array(
            'columns'       => 'parent_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'parentResource'
        ),
        'DependentResource' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'parent_id',
            'propertyName'  => 'dependentResources'
        ),
        'TagRef' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'tagRefs' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по item_type!
        ),
        'ClassifierLink' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Classifier_Link_LinkTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'classifierLinks' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по type!
        ),
        // связька для курс-итемов из ресурса
        'ResourceBookmark' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Course_Bookmark_BookmarkTable',
            'refColumns'    => 'resource_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'bookmarks'
        ),
        'MaterialCriteria' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Material_Criteria_CriteriaTable',
            'refColumns'    => 'material_id',
            'propertyName'  => 'materialCriteria' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по material_type!
        ),
    );

    public function getDefaultOrder()
    {
        return array('resources.title ASC');
    }
}