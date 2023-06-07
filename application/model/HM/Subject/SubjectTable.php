<?php

class HM_Subject_SubjectTable extends HM_Db_Table
{
    protected $_name = "subjects";
    protected $_primary = "subid";
    protected $_sequence = 'S_100_1_SUBJECTS';


    protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Role_TeacherTable",
        'HM_Subject_Course_CourseTable',
        'HM_Course_Item_History_HistoryTable',
        'HM_Course_Item_Current_CurrentTable',
        'HM_Subject_Mark_MarkTable',
        'HM_Role_DeanTable',
        'HM_Classifier_Link_LinkTable',
    );

    protected $_referenceMap = array(
        'SubjectUser' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_User_UserTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'subjectUser'
        ),
        'SubjectCriteria' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Criteria_CriteriaTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'subjectCriteria'
        ),
        'Student' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'students'),
        'Teacher' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Role_TeacherTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'teachers',
        ),
        'Claimant' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Role_ClaimantTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'claimants',
        ),
        'Graduated' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Role_GraduatedTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'graduated',
        ),
        'CourseAssign' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Course_CourseTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'courses',
        ),
        'ResourceAssign' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Resource_ResourceTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'resources',
        ),
/*        'TestAssign' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Test_TestTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'tests',
        ),
*/
        'TaskAssign' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Task_TaskTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'tasks',
        ),
        'ClassifierAssign' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Classifier_ClassifierTable',
            'refColumns' => 'subject_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'classifiers',
        ),
        'itemCurrent' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Course_Item_Current_CurrentTable',
            'refColumns'    => 'subject_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemCurrent'
        ),
        'itemHistory' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Course_Item_History_HistoryTable',
            'refColumns'    => 'subject_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemHistory'
        ),
        'Mark' =>array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Subject_Mark_MarkTable',
            'refColumns'    => 'cid',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'marks'
        ),
        'Progress' =>array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Subject_Progress_ProgressTable',
            'refColumns'    => 'subject_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'progresses'
        ),
        'Lesson' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'lessons'
        ),
        'LessonPollDean' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Lesson_Poll_Dean_DeanTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'lessons-poll-dean'
        ),
        'Dean' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_Role_DeanTable',
            'refColumns'    => 'subject_id',
            'propertyName'  => 'deans'
        ),
         'ClassifierLink' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Classifier_Link_LinkTable',
            'refColumns' => 'item_id',
            'propertyName' => 'classifierlinks'
        ),
        'Certificates' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Certificates_CertificatesTable',
            'refColumns' => 'subject_id',
            'propertyName' => 'certificates'
        ),
        'Scale' => array(
            'columns' => 'scale_id',
            'refTableClass' => 'HM_Scale_ScaleTable',
            'refColumns' => 'scale_id',
            'propertyName' => 'scale'
        ),
        'Formula' => array(
            'columns' => 'formula_id',
            'refTableClass' => 'HM_Formula_FormulaTable',
            'refColumns' => 'id',
            'propertyName' => 'formula'
        ),
        'CriterionTest' => array(
            'columns'       => 'subid',
            'refTableClass' => 'HM_At_Criterion_Test_TestTable',
            'refColumns'    => 'subject_id',
            'propertyName'  => 'criterionTest'
        ),
        'Programm' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns' => 'item_id', // нужно еще отфильтровать по type
            'propertyName' => 'programm'
        ),
        'Quest' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Quest_QuestTable',
            'refColumns' => 'subject_id',
            'propertyName' => 'quests'
        ),
        'Feedback' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Feedback_FeedbackTable',
            'refColumns' => 'subject_id',
            'propertyName' => 'feedbacks'
        ),
        'TcProvider' => array(
            'columns' => 'provider_id',
            'refTableClass' => 'HM_Tc_Provider_ProviderTable',
            'refColumns' => 'provider_id',
            'propertyName' => 'tcProvider'
        ),
        'TcApplication' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Tc_Application_ApplicationTable',
            'refColumns' => 'subject_id',
            'propertyName' => 'tcApplications'
        ),
        'SubjectRoom' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_Room_RoomTable',
            'refColumns' => 'cid',
            'propertyName' => 'rooms'
        ),
        'BaseSubject' => array(
            'columns' => 'base_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'baseSubject'
        ),
        'SessionSubject' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'base_id',
            'propertyName' => 'sessionSubject'
        ),
        'Feedback' => array(
            'columns' => 'subid',
            'refTableClass' => 'HM_Feedback_FeedbackTable',
            'refColumns' => 'subject_id',
            'propertyName' => 'feedbacks'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости



    public function getDefaultOrder()
    {
        return array('subjects.name ASC');
    }
}