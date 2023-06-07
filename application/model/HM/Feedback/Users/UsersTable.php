<?php

class HM_Feedback_Users_UsersTable extends HM_Db_Table
{
    protected $_name = "feedback_users";
    protected $_primary = "feedback_user_id";

    protected $_referenceMap = array(
        'Feedback' => array(
            'columns' => 'feedback_id',
            'refTableClass' => 'HM_Feedback_FeedbackTable',
            'refColumns' => 'feedback_id',
            'propertyName' => 'feedback'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('feedback_user_id ASC');
    }
}