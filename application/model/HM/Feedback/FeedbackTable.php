<?php

class HM_Feedback_FeedbackTable extends HM_Db_Table
{
    protected $_name = "feedback";
    protected $_primary = "feedback_id";

    protected $_referenceMap = array(
        'Quest' => array(
            'columns' => 'quest_id',
            'refTableClass' => 'HM_Quest_QuestTable',
            'refColumns' => 'quest_id',
            'propertyName' => 'quests'
        ),
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subect'
        ),
        'FeedbackUser' => array(
            'columns' => 'feedback_id',
            'refTableClass' => 'HM_Feedback_Users_UsersTable',
            'refColumns' => 'feedback_id',
            'propertyName' => 'feedbackUsers'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('feedback_id ASC');
    }
}