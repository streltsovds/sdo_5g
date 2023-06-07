<?php
class HM_Form_LessonDates extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('lessonDates');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                'module' => 'lesson',
                'controller' => 'list',
                'action' => 'index',
                'subject_id' => $this->getParam('subject_id', 0),
                'user_id' => $this->getParam('user_id', 0)
            ), null, true)
        ));

        /**
         * Открыта ли страница по ссылке из списка, а не из грида
         * 0 - нет
         * y - да
         * <int> - из списка по пользователю, ID пользователя
         **/
        $this->addElement('hidden', 'fromList', array(
            'Required' => false,
            'Value'    => 0,
        ));

        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'beginDate', array(
            'Label' => _('Дата начала'),
//            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateLessThanFormValue', false, array('name' => 'endDate'))
             ),
            'id' => "beginDate",
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'currentDate', array(
            'Label' => _('Дата'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1)
             ),
            'id' => "beginDate2",
            'Filters' => array('StripTags')
        ));

        $this->addElement($this->getDefaultTimePickerElementName(), 'beginTime', array(
            'Label' => _('Время начала'),
//            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/')
             ),
            'Filters' => array(

            )
        ));

        $this->addElement($this->getDefaultDatePickerElementName(), 'endDate', array(
            'Label' => _('Дата окончания'),
//            'Required' => true,
            'Validators' => array(
                array('StringLength', 50, 1),
                array('DateGreaterThanFormValue', false, array('name' => 'beginDate'))
             ),
            'id' => "endDate",
            'Filters' => array('StripTags')
        ));


        $this->addElement($this->getDefaultTimePickerElementName(), 'endTime', array(
            'Label' => _('Время окончания'),
//            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/'),
                array('DateTimeGreaterThanFormValues', false, array('minDateName' => 'currentDate', 'minTimeName' => 'beginTime', 'dateName' => 'currentDate'))
             ),
            'Filters' => array(

            )
        ));

        $groupDateArray = HM_Lesson_LessonModel::getDateTypes();
        // нет смысла в относительных датах, т.к. для конкретного юзера, даты известны
        unset($groupDateArray[HM_Lesson_LessonModel::TIMETYPE_RELATIVE]);

        $this->addElement('RadioGroup', 'GroupDate', array(
            'Label' => '',
        	'Value' => HM_Lesson_LessonModel::TIMETYPE_DATES,
            //'Required' => true,
            'MultiOptions' => $groupDateArray,
            'form' => $this,
            'dependences' => array(HM_Lesson_LessonModel::TIMETYPE_FREE => array(),
                HM_Lesson_LessonModel::TIMETYPE_DATES => array('beginDate', 'endDate'),
                HM_Lesson_LessonModel::TIMETYPE_TIMES => array('currentDate', 'beginTime', 'endTime'),
            )
        ));

        $this->addDisplayGroup(
            array('GroupDate',
            	'beginDate',
                'currentDate',
                'beginTime',
                'endDate',
                'endTime',
                'recommend',
                'beginRelative',
                'endRelative'
            ),
            'DateLessonGroup',
            array('legend' => _('Ограничение времени запуска'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
    }

    public function isValid($data)
    {
        // дополнительная валидация относительных дат: значения однознаковые, окончание всегда больше начала
        if ($data['begin_personal'] && $data['end_personal']) {
            $element = $this->getElement('end_personal');
            $element->addValidator('GreaterOrEqualThanValue',false,array('name' => 'begin_personal'));

            // если хотя бы одно число отрицательное, то оба значения д.б. < 0
            if ( min(intval($data['begin_personal']),intval($data['end_personal'])) < 0) {
                $element->addValidator('LessThan',false,array('max' => 0));
                $this->getElement('begin_personal')
                     ->addValidator('LessThan',false,array('max' => 0));
            }
        }
        return parent::isValid($data);
    }
}
