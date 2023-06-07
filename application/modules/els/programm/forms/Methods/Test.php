<?php
class HM_Form_Methods_Test extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_TEST;

    public function init() 
    {
        $this->addElement($this->getDefaultMultiSelectElementName(), $this->_typeElements[] = 'criteria', array(
            'Label' => _('Оцениваемые квалификации'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'class' => 'multiselect'
        ));
        
        parent::init(); // required!      
    }
    
    public function setDefaultsByEvaluation($evaluation)
    {
        $element = $this->getElement('criteria');
        $element->setOptions(array(
            'jQueryParams' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'criteria-test-list', 'evaluation_id' => $evaluation->evaluation_type_id)),
                'style' => 'width: 500px;'                
        )));
        
        parent::setDefaultsByEvaluation($evaluation);
    }    
}