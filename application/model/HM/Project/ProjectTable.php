<?php

class HM_Project_ProjectTable extends HM_Db_Table
{
    protected $_name = "projects";
    protected $_primary = "projid";
    protected $_sequence = 'S_100_1_PROJECTS';


    protected $_dependentTables = array(
        "HM_Role_ParticipantTable",
        "HM_Role_TeacherTable",
//        'HM_Project_Course_CourseTable',
        'HM_Course_Item_History_HistoryTable',
        'HM_Course_Item_Current_CurrentTable',
        'HM_Project_Mark_MarkTable',
        'HM_Role_CuratorTable',
        'HM_Classifier_Link_LinkTable',
//        'HM_Project_Progress_ProgressTable'
    );

    protected $_referenceMap = array(
        'Participant' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Role_ParticipantTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'participants'),
//        'Moderator' => array(
//            'columns' => 'projid',
//            'refTableClass' => 'HM_Role_ModeratorTable',
//            'refColumns' => 'project_id',
//            'onDelete' => self::CASCADE,
//            'propertyName' => 'moderators',
//        ),
        'Claimant' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Role_ClaimantTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'claimants',
        ),
        'Graduated' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Role_GraduatedTable',
            'refColumns' => 'CID',
            'onDelete' => self::CASCADE,
            'propertyName' => 'graduated',
        ),
        'CourseAssign' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Project_Course_CourseTable',
            'refColumns' => 'project_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'courses',
        ),
        'ResourceAssign' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Project_Resource_ResourceTable',
            'refColumns' => 'project_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'resources',
        ),
        'TestAssign' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Project_Test_TestTable',
            'refColumns' => 'project_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'tests',
        ),
        'TaskAssign' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Project_Task_TaskTable',
            'refColumns' => 'project_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'tasks',
        ),
        'ClassifierAssign' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Project_Classifier_ClassifierTable',
            'refColumns' => 'project_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'classifiers',
        ),
        'itemCurrent' => array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Course_Item_Current_CurrentTable',
            'refColumns'    => 'project_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemCurrent'
        ),
        'itemHistory' => array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Course_Item_History_HistoryTable',
            'refColumns'    => 'project_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemHistory'
        ),
        'MarkProject' =>array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Project_Mark_MarkTable',
            'refColumns'    => 'cid',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'marks'
        ),
        'Progress' =>array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Project_Progress_ProgressTable',
            'refColumns'    => 'project_id',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'progresses'
        ),
        'Meeting' => array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Meeting_MeetingTable',
            'refColumns'    => 'project_id',
            'propertyName'  => 'meetings'
        ),
        'Curator' => array(
            'columns'       => 'projid',
            'refTableClass' => 'HM_Role_CuratorTable',
            'refColumns'    => 'project_id',
            'propertyName'  => 'curators'
        ),
         'ClassifierLink' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Classifier_Link_LinkTable',
            'refColumns' => 'item_id',
            'propertyName' => 'classifierlinks'
        ),
        'Certificates' => array(
            'columns' => 'projid',
            'refTableClass' => 'HM_Certificates_CertificatesTable',
            'refColumns' => 'project_id',
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
    );// имя свойства текущей модели куда будут записываться модели зависимости



    public function getDefaultOrder()
    {
        return array('projects.name ASC');
    }
}