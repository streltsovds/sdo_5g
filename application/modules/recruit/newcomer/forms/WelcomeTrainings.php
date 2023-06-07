<?php
class HM_Form_WelcomeTrainings extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign-welcome-trainings');

        parent::init(); // required!
    }

    public function initWithData($postMassIds)
    {
        $this->addElement($this->getDefaultDatePickerElementName(), "date", array(
                'Label' => _('Дата'),
                'Required' => true,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50)
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

        $rooms = $this->getService('Room')->fetchAll()->getList('rid', 'name');

        $this->addElement($this->getDefaultSelectElementName(), "room", array(
            'Label' => _('Место проведения'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'filters' => array('int'),
            'multiOptions' => $rooms,
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), "edit", array(
            "Label" => "Скорректировать текст стандартного уведомления"
        ));

        $this->addDisplayGroup(array(
            'date',
            'room',
            'edit'
        ),
            "fieldset_welcome",
            array('legend' => _('Назначить на welcome-тренинг'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('action' => 'index'))
            )
        );

        $this->addElement('hidden',
            'postMassIds_grid',
            array(
                'value' => $postMassIds
            )
        );

        $this->addElement('hidden',
            'assignwelcome',
            array(
                'value' => 1
            )
        );

        parent::init(); // еще разочек

    }
}