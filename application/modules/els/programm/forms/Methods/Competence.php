<?php
class HM_Form_Methods_Competence extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE;
    
    public function init() 
    {
        $this->addElement($this->getDefaultMultiSelectElementName(), $this->_typeElements[] = 'criteria', array(
            'Label' => _('Оцениваемые компетенции'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'class' => 'multiselect'
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

        $element = $this->getElement('criteria');
        $element->setOptions(array(
            'jQueryParams' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'criteria-list', 'evaluation_id' => $evaluation->evaluation_type_id)),
                'style' => 'width: 500px;'                
        )));
        
        parent::setDefaultsByEvaluation($evaluation);
    }
}