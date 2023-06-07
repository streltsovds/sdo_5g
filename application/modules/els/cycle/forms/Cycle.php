<?php
class HM_Form_Cycle extends HM_Form
{
	public function init()
	{

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('cycle');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'cycle_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',false, array('min' => 1, 'max' => 255))
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $types = HM_Cycle_CycleModel::getCycleTypes(true);
        if (count($types)) {
            $this->addElement($this->getDefaultSelectElementName(), 'type', array(
                'Label' => _('Тип'),
                //            'Description' => _('При создании периода с типом "Оценка персонала" будут автоматически созданы показатели эффективности пользователей (на основе типовых показателей профиля должности).'),
                'readonly' => true,
                'multiOptions' => $types // only editable
            ));
        }

        $this->addElement($this->getDefaultDatePickerElementName(), 'begin_date', array(
                'Label' => _('Начало периода'),
                'Required' => true,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    ),
                    array(
                        'Date',
                        false,
                        array('format' => 'dd.MM.yyyy')
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'end_date', array(
                'Label' => _('Окончание периода'),
                'Required' => true,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
                    ),
                    array(
                        'DateGreaterThanFormValue',
                        false,
                        array('name' => 'begin_date')
                    ),
                    array(
                        'Date',
                        false,
                        array('format' => 'dd.MM.yyyy')
                    )
                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );
        $fields = array(
            'cancelUrl',
            'cycle_id',
            'name',
            'type',
            'begin_date',
            'end_date'
        );

        $this->addDisplayGroup(
            $fields,
            'testGroup1',
            array('legend' => _('Период'))
        );

		$this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}