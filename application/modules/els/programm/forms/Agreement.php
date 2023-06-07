<?php
class HM_Form_Agreement extends HM_Form {

    public function init() 
    {
        
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'required' => false,
                'value' => $_SERVER['HTTP_REFERER'],
            )
        );
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));
        
        
        $this->addElement($this->getDefaultTreeSelectElementName(), 'position_id', array(
            'Label' => _('Должность в оргструктуре'),
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'params' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 0))
            )
        ));
        

        $this->addDisplayGroup(array(
                'name',
                'position_id',
            ),
            'general',
            array('legend' => _('Общие свойства'))
        );
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));        
        
        parent::init(); // required!      
    }
}