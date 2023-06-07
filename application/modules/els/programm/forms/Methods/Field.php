<?php
class HM_Form_Methods_Field________________________ extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_FIELD;
    
    public function init() {
        
        $types = implode('_', HM_At_Evaluation_Method_FieldModel::getCriterionTypes());
        $this->addElement($this->getDefaultMultiSelectElementName(), $this->_typeElements[] = 'field-criteria', array(
            'Label' => _('Критерии, оцениваемые в ходе полевого обучения'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'remoteUrl' => $this->getView()->url(array('module' => 'profile', 'controller' => 'method', 'action' => 'competence-list', 'types' => $types))
        ));
        
        parent::init(); // required!
    }
}