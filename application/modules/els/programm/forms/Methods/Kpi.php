<?php
class HM_Form_Methods_Kpi extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_KPI;

    public function init() 
    {
        $criteria = Zend_Registry::get('serviceContainer')->getService('AtCriterionKpi')->fetchAll()->getList('criterion_id', 'name');
        $this->addElement($this->getDefaultMultiSelectElementName(), $this->_typeElements[] = 'criteria', array(
            'Label' => _('Оцениваемые критерии способа выполнения задач'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'multiOptions' => $criteria
        ));
        
        $this->addElement($this->getDefaultMultiTextElementName(), $this->_typeElements[] = 'memos', array(
            'Label' => _('Дополнительные поля формы'),
            'SubLabel' => _('Название поля %s'),
            'Required' => false,
            'class' => 'wide',
        ));
        
        parent::init(); // required!      
    }
    
    public function setDefaultsByEvaluation($evaluation)
    {
        $memos = Zend_Registry::get('serviceContainer')->getService('AtEvaluationMemo')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id), 'evaluation_memo_id')->getList('evaluation_memo_id', 'name');
        $element = $this->getElement('memos');
        $element->setValue($memos);
        
        $criterionIds = Zend_Registry::get('serviceContainer')->getService('AtEvaluationCriterion')->fetchAll(array('evaluation_type_id = ?' => $evaluation->evaluation_type_id))->getList('criterion_id');
        $element = $this->getElement('criteria');
        $element->setValue($criterionIds);
        
        parent::setDefaultsByEvaluation($evaluation);
    }
}