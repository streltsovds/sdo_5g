<?php
class HM_Form_Section extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('section');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => ''
        ));

        $this->addElement('hidden', 'key', array(
            'value' => 1,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement('hidden', 'oid', array(
            'value' => 1,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement('hidden', 'cid', array(
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => false
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                 array('StringLength', 255, 1)
            ),
            'Filters' => array('StripTags')
        ));

        $this->addDisplayGroup(
            array(
                'cid',
                'title'
            ),
            'importGroup',
            array('legend' => _('Раздел'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}
