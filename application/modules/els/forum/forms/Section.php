<?php

class HM_Form_Section extends HM_Form
{
    
    public function init(){

        $this->addElement('hidden','section_id', array('value' => 0, 'Required' => false));
        $this->addElement('hidden','cancelUrl', array('value' => $_SERVER['HTTP_REFERER'], 'Required' => false));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'label'        => _('Название').':',
            'required'     => true,
            'autocomplete' => 'off',
            'validators'   => array(
                array('StringLength', false, array('min' => 3, 'max' => 255))
            )
        ));
        
        $this->addDisplayGroup(array('title', 'section_id'), 'content', array('legend' => _('Добавить раздел')));
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Добавить')));
        
        parent::init();
    }
    
}