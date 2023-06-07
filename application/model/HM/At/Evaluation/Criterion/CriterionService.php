<?php
class HM_At_Evaluation_Criterion_CriterionService extends HM_Service_Abstract
{
    /**
     * Сравнивает новый список критериев с уже имеющимся; 
     * разницу дописывает, неиспользуемые более критерии удаляет 
     */
    public function assignCriteria($evaluationId, $criteria, $overrideQuests = array())
    {
        if (!is_array($criteria)) $criteria = array();
        
        $existingCriteria = $this->fetchAll($this->quoteInto('evaluation_type_id = ?', $evaluationId));
        $existingCriteriaIds = $existingCriteria->getList('criterion_id');
        
        $newCriteria = array_diff($criteria, $existingCriteriaIds);
        foreach ($newCriteria as $criterionId) {
            $this->insert(array(
                'evaluation_type_id' => $evaluationId,
                'criterion_id' => $criterionId,
                'quest_id' => isset($overrideQuests[$criterionId]) ? $overrideQuests[$criterionId] : 0, // для психоопросов возмоно переопределение quest_id на уровне вакансии
            ));
        }

        $removedCriteria = array_diff($existingCriteriaIds, $criteria);
        foreach ($removedCriteria as $criterionId) {
            $this->deleteBy($this->quoteInto(
                array('evaluation_type_id = ?', ' AND criterion_id = ?'),
                array($evaluationId, $criterionId)
            ));
        }
        
        if (count($overrideQuests)) {
            foreach ($existingCriteria as $existingCriterion) {
                if (!empty($overrideQuests[$existingCriterion->criterion_id]) && ($existingCriterion->quest_id != $overrideQuests[$existingCriterion->criterion_id])) {
                    $existingCriterion->quest_id = $overrideQuests[$existingCriterion->criterion_id];
                    $this->update($existingCriterion->getValues()); 
                }
            }
        }
    }
}