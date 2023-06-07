<?php

class HM_Subject_Quest_QuestTable extends HM_Db_Table
{
    protected $_name = "subjects_quests";
    protected $_primary = array("subject_id", "quest_id");

/*
     protected $_dependentTables = array(
        "HM_Role_StudentTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        ),
        'Quest' => array(
            'columns' => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns' => 'quest_id',
            'propertyName' => 'tests'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_courses.subject_id ASC');
    }
}