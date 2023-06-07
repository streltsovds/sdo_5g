<?php

class HM_Form_Import extends HM_Form {
    
    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('import');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                'module' => 'offline',
                'controller' => 'list',
                'action' => 'index'
                    ), null, true)
        ));
    
        $this->addElement('File', 'data', array(
                                                 'Label'      => _('Файл'),
                                                 'Required'   => false,
                                                 'Filters'    => array('StripTags'),
                                                 'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                                                 'file_size_limit' => 10485760,
                                                 'file_types' => '*.dat',
                                                 // псевдо снятие лимита
                                                 'file_upload_limit' => 1000
                                           )
        );

        $this->addDisplayGroup(array(
                'data',
            ), 
            'offline', 
            array('legend' => _('Оффлайн-версия'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        
        return parent::init();
    }

}