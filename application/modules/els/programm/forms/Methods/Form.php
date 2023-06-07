<?php
class HM_Form_Methods_Form extends HM_Form_Methods_Abstract {

    protected $_type = HM_At_Evaluation_EvaluationModel::TYPE_FORM;

    public function init() 
    {
// @todo: пока не понятно, понадобится ли
//         $this->addElement($this->getDefaultCheckboxElementName(), $this->_typeElements[] = 'simplified', array(
//             'Label' => _('Сокращенный вариант'),
//             'Description' => _('Если установлена эта опция, анкету не нужно заполнять'),
//             'Required' => false,
//             'Value' => 0,
//         ));
                
        parent::init(); // required!      
    }
}