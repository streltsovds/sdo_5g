<?php

class HM_Test_Abstract_AbstractTable extends HM_Db_Table
{
    protected $_name = "test_abstract";
    protected $_primary = "test_id";
    protected $_sequence = "S_65_1_TEST_ABSTRACT";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'test_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'testQuestions'
        ),
        'ProjectAssign' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_Resource_ResourceTable',
            'refColumns'    => 'subject_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'projects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'SubjectAssign' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_Subject_Quest_QuestTable',
            'refColumns'    => 'test_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'CriterionTest' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'test_id',
            'propertyName'  => 'criterionTest',
        ),
    );

    public function getDefaultOrder()
    {
        return array('questionnaires.quest_id ASC');
    }
}