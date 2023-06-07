<?php
class HM_At_Session_Event_Attempt_AttemptModel extends HM_Model_Abstract
{
    static public function factory($data, $default = 'HM_At_Session_Event_Attempt_AttemptModel')
    {
        if (isset($data['method']))
        {
            switch($data['method']) {
                case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                    return parent::factory($data, 'HM_At_Session_Event_Attempt_Method_CompetenceModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_KPI:
                    return parent::factory($data, 'HM_At_Session_Event_Attempt_Method_KpiModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_RATING:
                    return parent::factory($data, 'HM_At_Session_Event_Attempt_Method_RatingModel');
                    break;
                case HM_At_Evaluation_EvaluationModel::TYPE_FORM:
                    return parent::factory($data, 'HM_At_Session_Event_Attempt_Method_FormModel');
                    break;
            }
        }
        return parent::factory($data, $default);        
    }
}