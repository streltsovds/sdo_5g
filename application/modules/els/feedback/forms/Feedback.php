<?php
class HM_Form_Feedback extends HM_Form {

    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('feedback');

        if (!($subjectId = $this->getParam('subject_id', 0))) {
            $subjectId = $this->getParam('subid', 0);
        }

        $backUrlParams = array(
            'module'     => 'feedback',
            'controller' => 'list',
            'action'     => 'index'
        );

        if ($subjectId) {
            $backUrlParams['subject_id'] =  $subjectId;
        }

        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                        $backUrlParams,
                        null, true
                    )
            )
        );

        $this->addElement('hidden',
            'feedback_id',
            array(
                'Required' => true,
                'Validators' => array('Int'),
                'Filters' => array('Int')
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
        ));

        $this->addElement($this->getDefaultSelectElementName(), 'quest_id', array(
            'Label' => _('Опрос'),
            'Required' => true,
            'multiOptions' => HM_Feedback_FeedbackModel::getPolls(),
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
        ));

        if ($subjectId) {

             $this->addElement($this->getDefaultCheckboxElementName(), 'respondent_type', array(
                 'Label' => _('Назначить руководителю'),
                 'Required' => false,
                 'Description' => _('Если данная опция отмечена, мероприятие по сбору обратной связи будет назначено не самому пользователю, а его линейному руководителю.'),

             ));

            $this->addElement($this->getDefaultCheckboxElementName(), 'assign_new', array(
                'Label' => _('Назначать всем новым'),
                'Required' => false,
                'Description' => _('Если данная опция отмечена, мероприятие по сбору обратной связи будет назначаться всем новым пользователям, назначенным на данный курс.'),
            ));

        } else {

            $this->addElement($this->getDefaultCheckboxElementName(), 'assign_anonymous', array(
                'Label' => _('Разрешить анонимный доступ к опросу по прямой ссылке'),
                'Required' => false,
            ));

            $this->addElement('hidden',
                'assign_type',
                array(
                    'Value' => HM_Feedback_FeedbackModel::ASSIGN_NOW,
                )
            );

            $this->addElement('hidden',
                'assign_days',
                array(
                    'Value' => 0,
                )
            );
        }

        

        

        $students = array();
        $this->addElement($this->getDefaultMultiSelectElementName(), 'students',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'remoteUrl' => $this->getView()->url(array('module' => 'feedback', 'controller' => 'list', 'action' => 'get-students-ajax')),
                'multiOptions' => $students
            )
        );
        




        if ($subjectId) {
            $fieldsM = array(
                'subid',
                'name',
                'quest_id',
            );
            $fieldsS = array(
                'students',
                'respondent_type',
                'assign_new',
                'assign_teacher'
            );

        } else {
            $fieldsM = array(
                'subid',
                'name',
                'quest_id',
                'assign_type',
                'assign_days',
            );
            $fieldsS = array(
                'students',
                'assign_anonymous',

            );
        }


        $this->addDisplayGroup($fieldsM,
            'feedback_main',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup($fieldsS,
            'students_group',
            array('legend' => _('Участники'))
        );


        if($subjectId) {
            $this->addElement($this->getDefaultTextElementName(), 'assign_days', array(
                'Label' => _('Количество дней'),
                'Required' => false,
                'Validators' => array('Int'),
                'Filters' => array('Int'),
            ));

            $this->addElement('RadioGroup', 'assign_type', array(
                'Label' => _('Время назначения опроса респонденту'),
                'Required' => false,
                'form' => $this,
                'multiOptions' => HM_Feedback_FeedbackModel::getAssignTypes(),
                'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Value' => HM_Feedback_FeedbackModel::ASSIGN_NOW,
                'dependences' => array(
                    HM_Feedback_FeedbackModel::ASSIGN_AFTER_DAYS => array('assign_days'),
                )
            ));

            $this->addDisplayGroup(array(
                'assign_type',
                'assign_days',
            ),
                'feedback_assign',
                array('legend' => _('Время назначения'))
            );
        }


        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить'),
            'disabled' => 'disabled'
        ));

        $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_FILTER_FORM_SUBJECT_FEEDBACK);
        $this->getService('EventDispatcher')->filter($event, $this);

        parent::init(); // required!
    }

}