<?php
class HM_Form_MeetingStep1 extends HM_Form_SubForm
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('meetingStep1');

        $project = $this->getService('Project')->getOne($this->getService('Project')->find($this->getParam('project_id', 0)));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'meeting', 'controller' => 'list', 'action' => 'index', 'project_id' => $this->getParam('project_id', 0)), null, true)
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

        $this->addElement('hidden', 'meeting_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement($this->getDefaultTextElementName(), 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        $this->addElement('hidden', 'project_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        /*$collection = $this->getService('Project')->fetchAll(null, 'name');
        $projects = $collection->getList('subid', 'name');

        $this->addElement($this->getDefaultSelectElementName(), 'project_id', array(
            'Label' => _('Учебный курс'),
            'Required' => true,
            'Validators' => array(
                'Int',
                array('GreaterThan', false, 0)
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $projects
        ));

        */

        /*$collection = $this->getService('Event')->fetchAll(null, 'TypeName');
        $events = $collection->getList('TypeID', 'TypeName', _('Выберите инструмент обучения'));*/
        $project = $this->getService('Project')->getOne(
            $this->getService('Project')->find($this->getParam('project_id', 0))
        );

        $this->addElement($this->getDefaultSelectElementName(), 'event_id', array(
            'Label' => _('Тип занятия'),
            'Required' => true,
            'Validators' => array(
                'Int'
                //array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'Filters' => array('Int'),
            'MultiOptions' => ($project ? $project->getEventTypes() : array(_('Нет'))),
            'OnChange' => "if (this.value == 999) {this.value = 1000; return false;} if (this.value == Number('".HM_Event_EventModel::TYPE_POLL."')) $('#vedomost').attr('disabled', true); else $('#vedomost').attr('disabled', false);"
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
            'Required' => false,
            'Validators' => array(
                array('StringLength', 50, 1)
             ),
            'id' => "beginDate2",
            'Filters' => array('StripTags')
        ));

        $this->addElement('uiTimePicker', 'beginTime', array(
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


        $this->addElement('uiTimePicker', 'endTime', array(
            'Label' => _('Время окончания'),
//            'Required' => true,
            'Validators' => array(
                array('regex', false, '/^[0-9]{2}:[0-9]{2}$/'),
                array('DateTimeGreaterThanFormValues', false, array('minDateName' => 'currentDate', 'minTimeName' => 'beginTime', 'dateName' => 'currentDate'))
             ),
            'Filters' => array(

            )
        ));

       $this->addElement($this->getDefaultSelectElementName(), 'vedomost', array(
            'Label' => _('Мероприятие на оценку'),
            'Required' => false,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'MultiOptions' => array(
                0 => _('Нет'),
                1 => _('Да')
            )
        ));

        $groupDateArray = HM_Meeting_MeetingModel::getDateTypes();
        // Если базовый то скрываем ненужные поля
        if($project->isBase()){
            unset($groupDateArray[HM_Meeting_MeetingModel::TIMETYPE_DATES]);
            unset($groupDateArray[HM_Meeting_MeetingModel::TIMETYPE_TIMES]);

            $this->removeElement('beginDate');
            $this->removeElement('currentDate');
            $this->removeElement('beginTime');
            $this->removeElement('endDate');
            $this->removeElement('endTime');

            $this->addElement('hidden', 'all', array(
                'Required' => false,
                'Value' => true
            ));

        }

        $this->addElement('RadioGroup', 'GroupDate', array(
            'Label' => '',
        	'Value' => HM_Meeting_MeetingModel::TIMETYPE_DATES,
            //'Required' => true,
            'MultiOptions' => $groupDateArray,
            'form' => $this,
            'dependences' => array(HM_Meeting_MeetingModel::TIMETYPE_FREE => array(),
                HM_Meeting_MeetingModel::TIMETYPE_DATES => array('beginDate', 'endDate'),
                HM_Meeting_MeetingModel::TIMETYPE_TIMES => array('currentDate', 'beginTime', 'endTime'),
            )
        ));

        $this->addElement($this->getDefaultTextAreaElementName(),
                          'descript',
                          array(
                                'Label'      => _('Краткое описание'),
                                'Required'   => false,
                                'Validators' => array(),
                                'Filters'    => array('StripTags')
                          ));

        $tt = $this->addDisplayGroup(
            array('cancelUrl',
                  'meeting_id',
                  'title',
                  'project_id',
                  'event_id',
                  //'moderator',
				  //'moderator',
                  'vedomost',
                  'descript'
                  //'all'
            ),
            'CommonMeetingGroup',
            array('legend' => _('Общие свойства'))
        );

        $classifierElements = $this->addClassifierElements(
                HM_Classifier_Link_LinkModel::TYPE_MEETING,
                $this->getParam('meeting_id', 0)
        );
        $this->addClassifierDisplayGroup($classifierElements);

        $this->addDisplayGroup(
            array('GroupDate',
            	'beginDate',
                'currentDate',
                'beginTime',
                'endDate',
                'endTime',
            ),
            'DateMeetingGroup',
            array('legend' => _('Ограничение времени мероприятия'))
        );

        /*$this->addDisplayGroup(
            array('Condition',
            	'cond_progress',
                'cond_avgbal',
                'cond_sumbal',
                'cond_sheid',
                'cond_mark'
            ),
            'ConditionMeetingGroup',
            array('legend' => _('Условия запуска'))
        );*/

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Далее')));


       /* $this->addDisplayGroup(
            array(
                'cancelUrl',
                'meeting_id',
                'title',
                'project_id',
                'event_id',
                'moderator',
                'beginDate',
                'beginTime',
                'endDate',
                'endTime',
                'recommend',
                'vedomost',
                'all',
                'submit'
            ),
            'MeetingGroup',
            array('legend' => _('Параметры занятия'))
        );*/


        parent::init(); // required!
    }

    public function getElementDecorators($alias, $first = 'ViewHelper'){
        if($alias == 'recommend'){
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );

        }else{
            return parent::getElementDecorators($alias, $first);
        }



    }


    public function isValid($data) {
        // дополнительная валидация относительных дат: значения однознаковые, окончание всегда больше начала
        if ( $data['beginRelative'] && $data['endRelative'] ) {
            $element = $this->getElement('endRelative');
            $element->addValidator('GreaterOrEqualThanValue',false,array('name' => 'beginRelative'));

            // если хотя бы одно число отрицательное, то оба значения д.б. < 0
            if ( min(intval($data['beginRelative']),intval($data['endRelative'])) < 0) {
                $element->addValidator('LessThan',false,array('max' => 0));
                $this->getElement('beginRelative')
                     ->addValidator('LessThan',false,array('max' => 0));
            }
        }
        return parent::isValid($data);
    }

}
