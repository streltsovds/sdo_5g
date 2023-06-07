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
    
        $cycles = $this->getService('Cycle')->fetchAll($this->getService('Cycle')->quoteInto(
            array(
                'end_date >= ?',
            ),
            array(
                date('Y-m-d'),
            )
        ), array('begin_date'));

        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
            'Label' => _('Оценочный период'),
            'required' => true,
            'Validators' => array(
                'int',
                array('GreaterThan', false, array(0))
            ),
            'Filters' => array('Int'),
            'multiOptions' => $cycles->getList('cycle_id', 'name', '-'),
        ));
        
        $this->addElement($this->getDefaultFileElementName(), 'data', array(
            'Label' => _('Файл'),
            'Validators' => array(
                 array('Count', false, 1),
                 array('Extension', false, 'csv')
            ),
            'Required' => false,
            'Filters' => array('StripTags'),
            'Destination' => Zend_Registry::get('config')->path->upload->tmp,
            'file_size_limit' => 0,
            'file_types' => '*.csv',
            // псевдо снятие лимита
            'file_upload_limit' => 1
        ));

        $this->addDisplayGroup(array(
                'cycle_id',
                'data',
            ), 
            'offline', 
            array('legend' => _('Импортировать показатели эффективности'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        
        return parent::init();
    }

}