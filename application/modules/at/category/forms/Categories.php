<?php
class HM_Form_Categories extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('categories');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );
        
        $this->addElement('hidden',
            'category_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );        
        
        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 255)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );
        
        $this->addElement($this->getDefaultTextAreaElementName(), 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
        ));
        
        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'shortname',
            'description',
        ),
            'categories',
            array('legend' => _('Категория должности'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }
}