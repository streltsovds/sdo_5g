<?php
class HM_Form_Slider extends HM_Form
{

    public function init()
    {   
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('Slider');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));
        
       $this->addElement($this->getDefaultMultiSelectElementName(), 'in_slider',
           array(
               'Label' => '',
               'Required' => false,
               'Validators' => array(
                   'Int'
               ),
               'Filters' => array(
                   'Int'
               ),
               'remoteUrl' => $this->getView()->url(array('module' => 'subject', 'controller' => 'slider', 'action' => 'subjects-list')),
           )
       );

        
        $this->addDisplayGroup(
            array(
                'in_slider',
           ), 
           'Requirements', 
           array(
              'legend' => _('Курсы, отображаемые в витрине')
           )
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить'))
        );
      
        parent::init(); // required!
    }
}