<?php
class HM_Form_Methods_Abstract extends HM_Form {

    protected $_type;
    protected $_commonElements = array();
    protected $_typeElements = array();

    /**
     * @throws Zend_Form_Exception
     */
    public function init() 
    {
        $programmId = $this->getParam('programm_id', 0);

        $programm = $this->getService('Programm')->find($programmId)->current();

        $dismissType = array(HM_Programm_ProgrammModel::TYPE_ASSESSMENT);

        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName($this->_type);
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'index', 'submethod' => null))
            )
        );
                
        $this->addElement('hidden', 'evaluation_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
                
        $this->addElement('hidden', 'programm_event_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
                
        $this->addElement('hidden', 'method', array(
            'Required' => true,
        ));
                
        $this->addElement($this->getDefaultTextElementName(), $this->_commonElements[] = 'name', array(
            'Required' => true,
            'Label' => _('Название'),
        ));

        if(!in_array($programm->programm_type, $dismissType)){
            $this->addElement($this->getDefaultCheckboxElementName(), $this->_commonElements[] = 'hidden', array(
                'Required' => false,
                'Label' => _('Скрытый этап'),
            ));
        } else {
            $this->addElement('hidden', 'hidden', array(
                    'Required' => false,
                    'Value' => 0
                )
            );
        }


        $this->addDisplayGroup(
            array_merge($this->_commonElements, $this->_typeElements),
            'group_' . $this->_type,
            array('legend' => _('Настройки методики'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
//         $this->addElement('button', 'cancel', array('Label' => _('Отмена')));

        parent::init(); // required!
    }
    
    public function setDefaultsByEvaluation($evaluation)
    {
        $element = $this->getElement('evaluation_id');
        $element->setValue($evaluation->evaluation_type_id);

        $element = $this->getElement('method');
        $element->setValue($evaluation->method);

        if (count($evaluation->programmEvent)) {
            $programmEvent = $evaluation->programmEvent->current();
            $element = $this->getElement('programm_event_id');
            $element->setValue($programmEvent->programm_event_id);
            $element = $this->getElement('name');
            $element->setValue($programmEvent->name);
            $element = $this->getElement('hidden');
            $element->setValue($programmEvent->hidden);
        }
    }    
}