<?php
class HM_Form_Methods_Psycho extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO;

    public function init() 
    {
        $criteria = Zend_Registry::get('serviceContainer')->getService('AtCriterionPersonal')->fetchAll()->getList('criterion_id', 'name');
        $this->addElement($this->getDefaultMultiSelectElementName(), $this->_typeElements[] = 'criteria', array(
            'Label' => _('Оцениваемые личностные характеристики'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'multiOptions' => $criteria
        ));
        
        $criteria = Zend_Registry::get('serviceContainer')->getService('AtCriterionPersonal')->fetchAll();
        if (count($criteria)) {

            $quests = $questCriteria = array();
            $questCriteria = $criteria->getList('criterion_id', 'quest_id');
            if ($collection = Zend_Registry::get('serviceContainer')->getService('Quest')->fetchAll(array(
                'type = ?' => HM_Quest_QuestModel::TYPE_PSYCHO,
                'status = ?' => HM_Quest_QuestModel::STATUS_RESTRICTED
            ), 'name')) {
                $quests = array(0 => '') + $collection->getList('quest_id', 'name');
            }
            
            foreach ($criteria as $criterion) {
                $this->addElement($this->getDefaultSelectElementName(), $this->_typeElements[] = 'criterion_' . $criterion->criterion_id, array(
                    'Label' => sprintf(_('Опрос для оценки "%s"'), $criterion->name),
                    'Required' => true,
                    'Validators' => array('Int'),
                    'Filters' => array('Int'),
                    'multiOptions' => $quests,
                    'class' => 'wide criterion',
                    'value' => $questCriteria[$criterion->criterion_id],
                ));
            }
            
        }
        
        parent::init(); // required!      
    }
    
    public function setDefaultsByEvaluation($evaluation)
    {
        if (count($criteriaQuests = Zend_Registry::get('serviceContainer')->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id))->getList('criterion_id', 'quest_id'))) {
            
            $element = $this->getElement('criteria');
            $element->setValue(array_keys($criteriaQuests));
            
            foreach ($criteriaQuests as $criterionId => $questId) {
                $element = $this->getElement('criterion_' . $criterionId);
                $element->setValue($questId);
            }
        }                
        parent::setDefaultsByEvaluation($evaluation);
    }
}