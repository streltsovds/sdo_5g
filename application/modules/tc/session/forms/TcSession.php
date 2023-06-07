<?php
class HM_Form_TcSession extends HM_Form
{

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('tcsession');

        $this->addElement('hidden', 'cancelUrl', array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module' => 'session',
                        'controller' => 'list',
                        'action' => 'index'
                    )
                )
            )
        );

        $this->addElement('hidden', 'session_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getParam('session_id', 0)
        ));

        $this->addElement('hidden', 'responsible_id', array(
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getService('User')->getCurrentUserId()
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


//        // Так как планирование обучения строго на год, то задаем период не вручную, а автоматически
//        $cycles = $this->getService('Cycle')->fetchAll(array('type=?' => HM_Cycle_CycleModel::CYCLE_TYPE_PLANNING), 'begin_date')
//            ->getList('cycle_id', 'name', _('Выберите период планирования'));
//        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
//                'Label' => _('Период планирования'),
//                'Required' => true,
//                'Validators' => array(
//                    'Int',
//                    array('GreaterThan', false, array(
//                        'min' => 0,
//                        'messages' => array(
//                            Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка или добавить новый период"
//                        )
//                    ))
//                ),
//                'multiOptions' => $cycles,
//                'Filters' => array(
//                    'Int'
//                ),
//                'OptionType' => HM_Form_Decorator_AddOption::TYPE_CYCLE_TC
//            )
//        );

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

        $this->addElement($this->getDefaultSelectElementName(), 'year', array(
                'Label' => _('Период планирования'),
                'Required' => true,
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array(
                        'min' => 0,
                        'messages' => array(
                            Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать год"
                        )
                    ))
                ),
                'multiOptions' => $this->getService('TcSession')->getYearList(),
                'Filters' => array(
                    'Int'
                ),
                'Value' => date('Y') + 1
            )
        );

/* #26852
        $positionIdJQueryParams = array(
            'remoteUrl' => '/tc/session/ajax/departments',
            'width'     => 500,
            'height'    => 300,
        );

        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getActionName() == 'new') {
            $this->addElement('uiTreeSelect', 'checked_items', array(
                'Label' => _('Подразделения'),
                'required' => true,
                'validators' => array(
                    'int',
                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Значение обязательно для заполнения и не может быть пустым"))))
                ),
                'multiple' => true,
                'filters' => array('int'),
                'jQueryParams' => $positionIdJQueryParams
            ));

            //$this->addDisplayGroup(array(
            //        'checked_items',
            //    ),
            //    'departments',
            //    array('legend' => _('Участники оценки'))
            //);

        } else {
            $this->addElement($this->getDefaultTextAreaElementName(), 'checked_items_names', array(
                'Label' => _('Подразделения'),
                'Disabled' => true
            ));
            $this->addElement('hidden', 'checked_items');
        }
*/

 /*
        $this->addElement(new HM_Form_Element_FcbkComplete('planning_department', array(
                'Label' => _('Уровень планирования'),
                'json_url'   => '/tc/session/ajax/pdepartment',
                'Required'   => true,
                'newel'      => false,
                'maxitems'   => 1,
                'Filters'    => array()
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'norm', array(
                'Label' => _('Норматив на обучение одного пользователя (руб.)'),
                //'Required' => true,
                'Validators' => array(
                    array('StringLength',
                        255,
                        1
                    )
                ),
                'disabled' => true,
                'Filters' => array('StripTags'),
                'class' => 'brief2'
            )
        );
*/
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
                'session_id',
                'responsible_id',
                'name',
                'year',
                'checked_items',
                'checked_items_names',
                'date_begin',
                'date_end',
                'norm',
                'submit'
            ),
            _('Общие свойства')
        );

        parent::init();
    }

    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if ($alias == 'cycle_id') {
            return array (
                array($first),
                array('RedErrors'),
                array('AddOption'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt')),
            );
        } else {
            return parent::getElementDecorators($alias, $first);
        }
    }
}