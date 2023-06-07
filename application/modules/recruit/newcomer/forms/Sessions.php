<?php
class HM_Form_Sessions extends HM_Form {

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('assign-sessions');

        parent::init(); // required!
    }

    public function initWithData($subjects, $postMassIds)
    {
        $sessions = array();
        $subjectIds = array_keys($subjects);
        $collection = $this->getService('Subject')->fetchAll(array('base_id IN (?)' => $subjectIds));
        if (count($collection)) {
            foreach ($collection as $session) {
                if (!isset($sessions[$session->base_id])) $sessions[$session->base_id] = array();
                $sessions[$session->base_id][$session->subid] = $session->name;
            }
        }

        $collection = $this->getService('Room')->fetchAll(null, 'name');
        $rooms = array('');
        if ($collection) {
            foreach ($collection->getList('rid', 'name') as $rid => $name) {
                $rooms[$rid] = $name; // preserve keys
            }
        }

        foreach ($subjects as $subject) {

            $this->addElement('RadioGroup', "radio_{$subject->subid}", array(
                'Label' => '',
                'MultiOptions' => array(
                    0 => 'Оставить без изменений',
                    1 => 'Выбрать из уже имеющихся сессий',
                    2 => 'Создать новую учебную сессию',
                ),
                'value' => 0,
                'separator' => '',
                'form' => $this,
                'dependences' => array(
                    1 => array("subject_{$subject->subid}_sessionId"),
                    2 => array("subject_{$subject->subid}_begin", "subject_{$subject->subid}_end", "subject_{$subject->subid}_roomId"),
                )
            ));

            $this->addElement($this->getDefaultDatePickerElementName(), "subject_{$subject->subid}_begin", array(
                    'Label' => _('Дата начала'),
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

            $this->addElement($this->getDefaultDatePickerElementName(), "subject_{$subject->subid}_end", array(
                    'Label' => _('Дата окончания'),
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

            $this->addElement($this->getDefaultSelectElementName(), "subject_{$subject->subid}_roomId",
                array(
                    'Label' => _('Место проведения'),
                    'Required' => false,
                    'Filters' => array(
                        'Int'
                    ),
                    'multiOptions' => $rooms,
                )
            );

            if (isset($sessions[$subject->subid])) {
                $this->addElement($this->getDefaultSelectElementName(), "subject_{$subject->subid}_sessionId", array(
                    'Label' => _('Учебная сессия'),
                    'required' => true,
                    'validators' => array(
                        'int',
                        array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
                    ),
                    'filters' => array('int'),
                    'multiOptions' => isset($sessions[$subject->subid]) ? $sessions[$subject->subid] : array(),
                ));
            } else {
                $this->addElement('hidden', "subject_{$subject->subid}_sessionId", array());
            }

            $this->addDisplayGroup(array(
                    "radio_{$subject->subid}",
                    "subject_{$subject->subid}_sessionId",
                    "subject_{$subject->subid}_begin",
                    "subject_{$subject->subid}_end",
                    "subject_{$subject->subid}_roomId",
                ),
                "fieldset_{$subject->subid}",
                array('legend' => $subject->name)
            );
        }

        $this->addElement($this->getDefaultCheckboxElementName(), 'editnotifications', array(
            'label' => _('Скорректировать текст стандартного уведомления'),
        ));

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
            'assignsessions',
            array(
                'value' => 1
            )
        );

        parent::init(); // еще разочек

    }
}