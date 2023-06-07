<?php

class HM_Form_Offline extends HM_Form {
    
    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('offline');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                'module' => 'offline',
                'controller' => 'list',
                'action' => 'index'
                    ), null, true)
        ));
    
        $this->addElement($this->getDefaultTextElementName(), 'title', array('Label' => _('Название'),
            'Required' => true,
            )
        );

        $this->addDisplayGroup(array(
                'title',
            ), 
            'offline', 
            array('legend' => _('Оффлайн-версия'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        
        return parent::init();
    }

}