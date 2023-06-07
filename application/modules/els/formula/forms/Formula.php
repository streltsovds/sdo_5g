<?php
class HM_Form_Formula extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('room');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index', 'formula_id' => null, 'subject_id' => $this->getParam('subject_id', null)))
        ));

        $this->addElement('hidden', 'formula_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
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

        $types = HM_Formula_FormulaModel::getFormulaTypes();
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            unset($types[HM_Formula_FormulaModel::TYPE_SUBJECT]);
        }

        $this->addElement($this->getDefaultSelectElementName(), 'type', array(
            'Label' => _('Тип'),
            'Required' => true,
            'MultiOptions' => $types,
        ));

        $this->addElement($this->getDefaultTextAreaElementName(), 'formula', array(
            'Label' => _('Формула '),
            'Description' => _('Пример формулы').': ' . nl2br(HM_Formula_FormulaModel::getFormulaExample()),
            'rows' => 7,
            'Required' => true,
            'Filters' => array(
                'StripTags'
            )
        ));

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'formula_id',
                'name',
                'type',
                'formula',
                'submit'
            ),
            'projectGroup',
            array('legend' => _('Формула'))
        );

        parent::init(); // required!
	}

}