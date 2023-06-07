<?php
class HM_Form_Sessions extends HM_Form {

    public function init()
    {
        if ($sessionId = $this->getParam('session_id', 0)) {
            $this->addElement('hidden', 'session_id');
        }

        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('sessions');

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(array('controller' => 'list', 'action' => 'index', 'session_id' => null))
            )
        );

        $this->addElement('hidden',
            'programm_type',
            array(
                'Required' => false,
                'Value' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT
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

        $this->addElement($this->getDefaultTextElementName(), 'shortname', array(
            'Label' => _('Краткое название'),
            'Validators' => array(
                array('StringLength',
                    false,
                    array('min' => 1, 'max' => 24)
                )
            ),
            'Filters' => array('StripTags'),
            'class' => 'wide'
        )
        );

        $cycles = $this->getService('Cycle')->fetchAll(
            array(
                'newcomer_id IS NULL' => '',
                'type = ?' => HM_Cycle_CycleModel::CYCLE_TYPE_ASSESMENT,
            ), 'begin_date')->getList('cycle_id', 'name', _('Выберите период оценки'));

        $this->addElement($this->getDefaultSelectElementName(), 'cycle_id', array(
            'Label' => _('Период оценки'),
            'required' => false,
            'validators' => array(
                'int',
            ),
            'filters' => array('int'),
            'multiOptions' => $cycles,
            'OptionType' => HM_Form_Decorator_AddOption::TYPE_CYCLE
        ));


        $this->addElement($this->getDefaultDatePickerElementName(), 'begin_date', array(
            'Label' => _('Дата начала сессии'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
                )
            ),
            'Value' => date('d.m.Y'),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            )
        )
        );

        $this->addElement($this->getDefaultDatePickerElementName(), 'end_date', array(
            'Label' => _('Дата окончания сессии'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
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

        $this->addDisplayGroup(array(
            'cancelUrl',
            'name',
            'shortname',
        ),
            'general',
            array('legend' => _('Общие свойства'))
        );

        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getActionName() == 'new') {

            $positionIdJQueryParams = array(
//                 'remoteUrl' => $this->getView()->url(array('module' => 'orgstructure', 'controller' => 'ajax', 'action' => 'tree', 'only-departments' => 1))
                'remoteUrl' => '/orgstructure/ajax/tree/only-departments/1',
                'width' => 500,
                'height' => 300,
            );

            $this->addElement($this->getDefaultTreeSelectElementName(), 'checked_items', array(
                'Label' => _('Подразделение'),
                'required' => true,
                'validators' => array(
                    'int',
                    array('GreaterThan', false, array(-1))
                ),
                'multiple' => true,
                'filters' => array('int'),
                'params' => $positionIdJQueryParams
            ));

            $this->addDisplayGroup(array(
                'checked_items',
            ),
                'departments',
                array('legend' => _('Участники оценки'))
            );

        } else {
            $this->addElement('hidden', 'checked_items');
        }
        
        $this->addElement('hidden', 'item_type', array(
            'value' => 'soid'        
        ));

        $this->addDisplayGroup(array(
            'cycle_id',
            'begin_date',
            'end_date',
        ),
            'sessionPeriodGroup',
            array('legend' => _('Ограничение времени проведения'))
        );




        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Комментарий к оценочной сессии'),
            'Description' => _('Комментарий отображается на странице со списком оценочных сессий пользователя'),
            'Required' => false,
            'class' => 'wide',
            )
        );

//        $this->addElement($this->getDefaultWysiwygElementName(), 'report_comment', array(
//            'Label' => _('Общий комментарий к индивидуальному отчету'),
//            'Description' => _('Комментарий отображается на странице индивидуального отчета пользователя по итогам оценочной сессии'),
//            'Required' => false,
//            'class' => 'wide'
//        )
//        );

        $this->addDisplayGroup(array(
            'description',
//            'report_comment',
        ),
            'comments',
            array('legend' => _('Комментарии для пользователей'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
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