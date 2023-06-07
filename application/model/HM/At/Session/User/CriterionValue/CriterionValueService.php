<?php
class HM_At_Session_User_CriterionValue_CriterionValueService extends HM_Service_Abstract
{
    // Метод для расчёта и сохранения результирующей оценки по компетенциям;
    // для других видов критериев расчёт не требуется результат сохраняется сразу по получении
    public function setCriteriaValues($sessionUserId)
    {
        $sessionUser = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators'), $sessionUserId)->current();
        
        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $sessionUser->session->current()->getOptionsModifier());
        
        // @todo: оценка может и не включать в себя 360
        // но чудесным образом и для парных сравнений оно работает 
        $results = $this->getService('AtEvaluation')->profileResultsByRelationType($sessionUser, $options);
        $results = $results['results'];

        $this->deleteBy(array(
            'session_user_id = ?' => $sessionUserId,
            'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
        ));
        foreach ($results as $criterionId => $result) {
            $this->insert(array(
                'session_user_id' => $sessionUserId,
                'criterion_id' => $criterionId,
                'criterion_type' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                'value' => $result['criterion'][HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_OTHERS],
            ));
        }
        return true;
    }
}