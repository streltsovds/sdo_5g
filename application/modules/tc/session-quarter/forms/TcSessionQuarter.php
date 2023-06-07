<?php
class HM_Form_TcSessionQuarter extends HM_Form
{

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('tcsessionquarter');

        $this->addElement('hidden', 'cancelUrl', array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module' => 'session-quarter',
                        'controller' => 'list',
                        'action' => 'index'
                    )
                )
            )
        );

        $this->addElement('hidden', 'session_quarter_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getParam('session_quarter_id', 0)
        ));


        $this->addElement($this->getDefaultTextElementName(), 'name', array(
                'Label' => _('Название'),
                'Required' => true,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'Filters' => array('StripTags'),
                'class' => 'wide'
            )
        );

        $sessionsCollection = $this->getService('TcSession')->fetchAll(
                array(
                    'type=?' => HM_Tc_Session_SessionModel::TYPE_TC,
                    'status=?' => HM_Tc_Session_SessionModel::FINISHED
                ), 
                'name');
        $sessions = array();
        foreach ($sessionsCollection as $session) {
            $state = $this->getService('State')->fetchAll(array(
                'item_id=?' => $session->session_id,
                'process_type=?' => HM_Process_ProcessModel::PROCESS_TC_SESSION,
                'status=?' => 3
            ))->current();
            if ($state->item_id) $sessions[$session->session_id] = $session->name;
        }
        $sessions = array(_('---выберите сессию---')) + $sessions;

        $this->addElement($this->getDefaultSelectElementName(), 'session_id', array(
                'Label' => _('Сессия годового планирования'),
                'Required' => true,
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array(
                        'min' => 0,
                        'messages' => array(
                            Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать сессию годового планирования"
                        )
                    ))
                ),
                'multiOptions' => $sessions,
                'Filters' => array(
                    'Int'
                )
            )
        );


        $this->addElement($this->getDefaultSelectElementName(), 'quarter', array(
                'Label' => _('Квартал'),
                'Required' => true,
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array(
                        'min' => 0,
                        'messages' => array(
                            Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать квартал"
                        )
                    ))
                ),
                'multiOptions' => $this->getService('TcSessionQuarter')->getQuarterList(),
                'Filters' => array(
                    'Int'
                ),
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'date_begin', array(
                'Label' => _('Дата начала сессии планирования'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50, 'messages' => _('Неверный формат даты'))
                    ),

                ),
                'Filters' => array('StripTags'),
                'JQueryParams' => array(
                    'showOn' => 'button',
                    'buttonImage' => "/images/icons/calendar.png",
                    'buttonImageOnly' => 'true'
                )
            )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'date_end', array(
                'Label' => _('Дата окончания сессии планирования'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'StringLength',
                        false,
                        array('min' => 10, 'max' => 50, 'messages' => _('Неверный формат даты'))
                    ),
                    array(
                        'DateGreaterThanFormValue',
                        false,
                        array('name' => 'date_begin')
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

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
                'name',
                'session_id',
                'quarter',
                'date_begin',
                'date_end',
                'submit'
            ),
            _('Общие свойства')
        );

        parent::init();
    }

}