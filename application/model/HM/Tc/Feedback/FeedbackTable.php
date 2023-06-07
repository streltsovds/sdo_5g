<?php

class HM_Tc_Feedback_FeedbackTable extends HM_Db_Table
{
    protected $_name = "tc_feedbacks";
    protected $_primary = array("subject_id", "user_id");
    protected $_sequence = "S_106_1_TC_FEDDBACKS";

    public function getDefaultOrder()
    {
        return array('tc_feedbacks.date DESC');
    }
}