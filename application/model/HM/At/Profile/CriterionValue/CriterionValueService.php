<?php
class HM_At_Profile_CriterionValue_CriterionValueService extends HM_Service_Abstract
{
    // теперь у нас есть view `criteria`
    public function getSelect($criterionType, $fields)
    {
        $select = parent::getSelect();
        
        switch ($criterionType) {
        case HM_At_Criterion_CriterionModel::TYPE_CORPORATE:
            $table = 'at_criteria';
            break;
        case HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL:
            $table = 'at_criteria_test';
            break;
        case HM_At_Criterion_CriterionModel::TYPE_PERSONAL:
            $table = 'at_criteria_personal';
            break;
        }
                
        $select->from(array('ac' => $table), $fields);
        
        return $select;
        
    }
    
    public function assign($profileId, $criteriaIds, $criterionType)
    {
        if (!is_array($criteriaIds)) {
            $criteriaIds = array($criteriaIds);
        }        
        
        $currentCriteriaIds = $newCriteriaIds = array();
        if (count($collection = $this->fetchAll(array('profile_id = ?' => $profileId, 'criterion_type = ?' => $criterionType)))){
            $currentCriteriaIds = $collection->getList('criterion_id');
        }
        foreach ($criteriaIds as $criterionId) {
            if (in_array($criterionId, $currentCriteriaIds)) continue;
            $this->insert(array(
                'criterion_id' => $criterionId,        
                'criterion_type' => $criterionType,        
                'profile_id' => $profileId,        
            ));
            $newCriteriaIds[] = $criterionId;
        }
                
        // нужно еще обновить свойства всех методик программ подбора и оценки
        // то же самое делается через маленький карандашик в вакансии 
        if ($profileId && ($methods = HM_At_Evaluation_EvaluationModel::getSubjectMethod($criterionType))) {
            $evaluations = $this->getService('AtEvaluation')->fetchAllDependence('EvaluationCriterion', array(
                'profile_id = ?' => $profileId,
                'method IN (?)' => $methods,
            ));
            $currentCriteriaIds = array();
            foreach ($evaluations as $evaluation) {
                if (count($evaluation->evaluation_criterion)) {
                    $currentCriteriaIds = $evaluation->evaluation_criterion->getList('criterion_id');
                }
                foreach ($newCriteriaIds as $criterionId) {
                    if (in_array($criterionId, $currentCriteriaIds)) continue;
                    $this->getService('AtEvaluationCriterion')->insert(array(
                        'criterion_id' => $criterionId,        
                        'evaluation_type_id' => $evaluation->evaluation_type_id,        
                    ));
                }
            }
        }
        
        return true;
    }
    
    public function unassign($profileId, $criteriaIds, $criterionType)
    {
        if (is_array($criteriaIds) && count($criteriaIds)) {
            $this->deleteBy(array(
                'profile_id = ?' => $profileId, 
                'criterion_id IN (?)' => $criteriaIds, 
                'criterion_type = ?' => $criterionType,
            ));
        }
        
        // нужно еще обновить свойства всех методик программ подбора и оценки
        // то же самое делается через маленький карандашик в вакансии 
        if ($profileId && ($methods = HM_At_Evaluation_EvaluationModel::getSubjectMethod($criterionType))) {
            $evaluations = $this->getService('AtEvaluation')->fetchAllDependence('EvaluationCriterion', array(
                'profile_id = ?' => $profileId,
                'method IN (?)' => $methods,
            ));
            foreach ($evaluations as $evaluation) {
                if (count($evaluation->evaluation_criterion)) {
                    $this->getService('AtEvaluationCriterion')->deleteBy(array(
                        'evaluation_type_id = ?' => $evaluation->evaluation_type_id,
                        'criterion_id IN (?)' => $criteriaIds,
                    ));
                }
            }
        }        
        return true;
    }    
}