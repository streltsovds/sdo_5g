<?php
class HM_Form_HolidayPeriodic extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('holidays');
        $this->setAttrib('onSubmit', 'if (confirm("'._('В результате будут удалены и заново созданы все периодические выходные дни, начиная с настоящего момента. Продолжить?').'")) return true; return false;');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'default','action' => 'index', 'controller' => 'index'))
        ));

        $weekdays = HM_Date::getWeekdays();
        $this->addElement($this->getDefaultMultiCheckboxElementName(),
                          'holidays',
                          array(
				            'Required' => false,
				            'Label' => '',
				            'MultiOptions' => $weekdays
                          )
            );

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'holidays',
                'submit'
            ),
            'resourceGroup',
            array('legend' => _('Выходные дни'))
        );

        parent::init(); // required!
	}

}
