<?php 
class HM_Form_Sections extends HM_Form
{
	public function init()
	{
        $id = $this->getParam('id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(($resourceId ? array('action' => 'index', 'resource_id' => $resourceId) : array('action' => 'index')))
        ));
        
        $this->addElement('hidden', 'project_id', array(
            'Required' => false,
        ));
        
        $this->addElement('hidden', 'section_id', array(
            'Required' => false,
        ));
        
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
        
		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'name',
                'submit'
            ),
            'resourceGroup',
            array('legend' => '')
        );

        parent::init(); // required!
	}

}
