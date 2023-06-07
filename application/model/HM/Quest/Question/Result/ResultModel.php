<?php
class HM_Quest_Question_Result_ResultModel extends HM_Model_Abstract
{
    const CLUSTER_STATUS_NOT_STARTED = 0;
    const CLUSTER_STATUS_IN_PROGRESS = 1;
    const CLUSTER_STATUS_FINISHED = 2;

    protected $_primaryName = 'question_result_id';
}