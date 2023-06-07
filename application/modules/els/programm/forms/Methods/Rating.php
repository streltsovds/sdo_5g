<?php
class HM_Form_Methods_Rating extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_RATING;

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
            'remoteUrl' => $this->getView()->url(array('module' => 'profile', 'controller' => 'evaluation', 'action' => 'criteria-list'))
        ));

        parent::init(); // required!
    }
    

    public function setDefaultsByEvaluation($evaluation)
    {
        $element = $this->getElement('criteria');
        $element->setOptions(array(
            'jQueryParams' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'profile', 'controller' => 'evaluation', 'action' => 'criteria-list', 'evaluation_id' => $evaluation->evaluation_type_id)),
                'style' => 'width: 500px;'                
        )));
        
        parent::setDefaultsByEvaluation($evaluation);
    }    
}