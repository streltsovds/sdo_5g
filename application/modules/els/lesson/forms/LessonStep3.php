<?php
class HM_Form_LessonStep3 extends HM_Form_SubForm
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('lessonStep3');

        $session = $this->getSession();
        $isManualAssign = ($session['step2']['assign_type'] == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL);

        $prevSubForm = 'step2';
        if ($this->getParam('subForm', false) == 'step2') {
            $prevSubForm = 'step1';
        }
        $this->addElement('hidden', 'prevSubForm', array(
            'Required' => false,
            'Value' => $prevSubForm
        ));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
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
                'remoteUrl' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'ajax', 'action' => 'students-list')),
                'multiOptions' => $students
            )
        );

        /*
        $groups = array();
        $this->addElement($this->getDefaultMultiSelectElementName(), 'groups',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'jQueryParams' => array(
                    'remoteUrl' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'ajax', 'action' => 'groups-list'))
                ),
                'multiOptions' => $groups,
                'class' => 'multiselect'
            )
        );
        */

        $groups = $this->getService('Group')->fetchAll(array('cid = ?' => $this->getParam('subject_id', 0)));
        $studygroups = $this->getService('StudyGroupCourse')->getCourseGroups($this->getParam('subject_id', 0));
        $groupsSelect = array();

        if ($studygroups) {
            $groupsSelect[] =  _('-Группы-');
            foreach ($studygroups as $studygroup) {
                $groupsSelect['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }

        if (count($groups)) {
            $groupsSelect[] =  _('-Подгруппы-');
            foreach ($groups as $item) {
                $groupsSelect['s_'.$item->gid] = $item->name;
            }
        }

        $this->addElement($this->getDefaultSelectElementName(), 'subgroups',
            array(
                'Label' => '',
                'multiOptions' => $groupsSelect
            )
        );

//        $groups = $this->getService('Group')->fetchAll(array('cid = ?' => $this->getParam('subject_id', 0)));
//        $groups = $groups->getList('gid', 'name', _('Выберите подгруппу'));
//        $this->addElement($this->getDefaultSelectElementName(), 'subgroups',
//            array(
//            	'Label' => '',
//            	'multiOptions' => $groups
//            )
//        );


        $this->addElement('RadioGroup', 'switch', array(
            'Label' => '',
        	'Value' => 0,
            //'Required' => true,
            'MultiOptions' => array(0 => _('Все слушатели курса'), 1 => _('Список слушателей'), 2 /*=> _('Группы'),  3*/=> _('Группа/Подгруппа')),
            'form' => $this,
            'dependences' => array(1 => array('students'),
                                   2 /*=> array('groups'),
                                   3 */=> array('subgroups')
                             )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'all', array(
            'Label' => _('Автоматически назначать всем новым слушателям курса'),
            'Required' => false,
            //'Validators' => array('Int'),
            //'Filters' => array('Int'),
            'Value' => 1,
            /*'MultiOptions' => array(
                0 => _('Нет'),
                1 => _('Да')
            ) */
            //'Checked' => true
        ));

        $displayArray = array("teacher");
        $teacherRequired = $session['step1']['has_proctoring'];

        $session = $this->getSession();
        $eventId = $session['step1']['event_id'];
        if ($eventId < 0) {
            $event = $this->getService('Event')->getOne(
                $this->getService('Event')->find(-$eventId)
            );

            if ($event) {
                $eventId = $event->tool;
            }
        }

        switch($eventId) {
            case HM_Event_EventModel::TYPE_TEST:
                /**
                 * Индивидульная настройка теста для занятия.
                 * Раскомментировать при необходимости.
                 * переносим на 2й шаг
                 */
                //$this->initQuest();
                break;
        }

        switch($eventId) {
            case HM_Event_EventModel::TYPE_EXERCISE:
            case HM_Event_EventModel::TYPE_ECLASS:
//                $teacherRequired = true;
                $teacherRequired = false;
                break;
            case HM_Event_EventModel::TYPE_WEBINAR:
                $teacherRequired = true;
                $displayArray[] = "moderator";
                break;
        }

        $teachers = $teacherRequired?array():array(0 => _('Нет'));
		$moderators = array(/*0 => _('Нет')*/);
        $collection = $this->getService('Teacher')->fetchAllDependence(
            'User',
            $this->getService('Teacher')->quoteInto('CID = ?', $this->getParam('subject_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $teacher = $item->getUser();
                if ($teacher) {
                    $teachers[$teacher->MID] = $teacher->getName();
                    $moderators[$teacher->MID] = $teacher->getName();
                }
            }
        }


        $collection = $this->getService('Student')->fetchAllDependence(
            'User',
            $this->getService('Student')->quoteInto('CID = ?', $this->getParam('subject_id', 0))
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $moderator = $item->getUser();
                if ($moderator) {
                    $moderators[$moderator->MID] = $moderator->getName();
                }
            }
        }

        asort($moderators);
        $moderators_list = array(0 => _('Нет'));
        foreach ($moderators as $key=>$value) {
            $moderators_list[$key] = $value;
        }

        if (in_array("teacher", $displayArray)) {
            $this->addElement($this->getDefaultSelectElementName(), 'teacher', array(
                'Label' => _('Тьютор'),
                'Required' => $teacherRequired,
                'Validators' => array(
                    'Int',
                    //array('GreaterThan', false, array(0))
                ),
                'Filters' => array(
                    'Int'
                ),
                'MultiOptions' => $teachers
            ));
        }

        if (in_array("moderator", $displayArray)) {
            $this->addElement($this->getDefaultSelectElementName(), 'moderator', array(
                'Label' => _('Модератор'),
                'Required' => false,
                'Validators' => array(
                    'Int',
                    //array('GreaterThan', false, array(0))
                ),
                'Filters' => array(
                    'Int'
                ),
                'MultiOptions' => $moderators_list
            ));
        }

        if (count($displayArray)) {
            $this->addDisplayGroup(
                $displayArray,
                'LessonControl',
                array('legend' => _('Тьютор'))
            );
        }



        $this->addDisplayGroup(
            array('switch',
                'prevSubForm',
                'cancelUrl',
                'lesson_id',
                'subject_id',
                'students',
                /*'groups',*/
            	'subgroups',
                'all',
                //'submit'
            ),
            'LessonGroup',
            array('legend' => _('Участники'))
        );

        //#20718: нужно чтобы не дизейблился сабмит при ручном назначении
        $submitDisabled = 'disabled';
        // ручное назначение вариантов заданий

        if ($isManualAssign) {

            $this->removeElement('switch');
            $this->removeElement('students');
            /*$this->removeElement('groups');*/
            $this->removeElement('subgroups');
            $this->removeElement('all');
            $this->removeDisplayGroup('LessonGroup');
            
            $submitDisabled = null;

            $where           = $this->getService('Student')->quoteInto('CID=?', $this->getParam('subject_id', -1));
            $subjectStudents = $this->getService('Student')->fetchAllDependence('User', $where);
            $studentsList    = array();

            if (count($subjectStudents)) {
                foreach ($subjectStudents as $student) {
                    if (count($student->users)) {
                        foreach ($student->users as $user) {
                            $studentsList[$user->MID] = $user->getName();
                        }
                    }
                }
            }

            $taskId       = $session['step2']['module'];
            $task         = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
            $ids          = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $task->data);
            $questionList = $this->getService('Question')->fetchAll(array('kod IN(?)' => $ids))->getList('kod','qtema');
            $userVariants = array();

            foreach ($questionList as &$value) $value = substr(strip_tags($value), 0, 100);

            if ($this->getParam('lesson_id',0)) {
                $interviews = $this->getService('Interview')->fetchAll(array(
                    'lesson_id=?' => (int) $this->getParam('lesson_id',0),
                ));
                if (count($interviews)) {
                    foreach ($interviews as $itwItem) {
                        if ((array_key_exists($itwItem->user_id, $studentsList) || 
                                array_key_exists($itwItem->to_whom, $studentsList)
                            ) && $itwItem->question_id
                        ) {
                            $userId = (array_key_exists($itwItem->user_id, $studentsList))? $itwItem->user_id : $itwItem->to_whom;
                            $userVariants[$userId] = $itwItem->question_id;
                        }
                    }
                }
            }
            $this->addElement(
                'associativeSelect',
                'user_variant',
                array(
                    'Label'  => _('Варианты'),
                    'keys'   => $studentsList,
                    'values' => $questionList,
                    'Value'  => $userVariants
                )
            );

            $this->addDisplayGroup(
                array('user_variant'),
                'LessonVariants',
                array('legend' => _('Назначение вариантов участникам'))
            );
        }

      if($event->event_id==HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY || $event->event_id==HM_Event_EventModel::TYPE_OLYMPOX_EXAM) {

        $this->addElement('RadioGroup', 'notify', array(
            'Label' => '',
        	'Value' => $event->event_id==HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY ? 1 : 0,
            'Description' => _('Уведомление  о необходимости прохождения занятия будет отправляться за указанное количество дней до окончания занятия'),
            'MultiOptions' => array(0=>'Не уведомлять', 1=>'Уведомлять до окончания занятия'),
            'form' => $this,
            'dependences' => array(0 => array(),1 => array('notify_before'))
        ));
        $this->addElement($this->getDefaultTextElementName(), 'notify_before', array(
            'Label' => _('за (дней)'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => $event->event_id==HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY ? HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY_DAYS : 0
        ));
        $this->addDisplayGroup(array(
            'notify',
            'notify_before',
            ),
            'notifyBeforeGroup',
            array('legend' => _('Уведомление участников'))
        );
      }


        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить'),
            'disabled' => $submitDisabled
        ));

        parent::init(); // required!
    }

    public function initQuest() {

        $lessonId = (int) $this->getParam('lesson_id', 0);
        $subjectId = (int) $this->getParam('subject_id', 0);
        $session = $this->getSession();
        $questId = $session['step2']['module'];

        $clusters      = array();
        $clusterIds    = array();
        if ($questId) {
            $clusters = $this->getService('QuestCluster')->fetchAll(array('quest_id=?' => $questId))->getList('cluster_id', 'name');
            foreach ($clusters as $clusterId => $clusterName) {
                $clusterIds[] = 'cluster_limit_'. $clusterId;
            }
        }

        $this->addElement('RadioGroup', 'mode_selection', array(
            'Label' => _('Выборка вопросов'),
            'MultiOptions' => HM_Quest_QuestModel::getSelectionModes(),
            'separator' => '',
            'form' => $this,
            'dependences' => array(
                HM_Quest_QuestModel::MODE_SELECTION_ALL => array('mode_selection_all_shuffle'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT => array('mode_selection_questions'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER => array('mode_selection_questions'),
                HM_Quest_QuestModel::MODE_SELECTION_LIMIT_CLUSTER => $clusterIds,
            )
        ));

        foreach ($clusters as $clusterId => $clusterName) {
            $this->addElement($this->getDefaultTextElementName(), 'cluster_limit_'. $clusterId, array(
                'Label'    => $clusterName,
                'Required' => false,
                'Filters'  => array('Int'),
            ));
        }

        $this->addElement($this->getDefaultTextElementName(), 'mode_selection_questions', array(
            'Label' => _('Количество вопросов, выбранных случайным образом'),
            'Description' => _('Если общее количество вопросов в тесте или в каком-то блоке вопросов меньше, чем данный параметр, в таком случае будут выбраны все имеющиеся вопросы.'),
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'mode_selection_all_shuffle', array(
            'Label' => _('Перемешивать вопросы'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limit_attempts', array(
            'Label' => _('Ограничение по количеству попыток'),
            'Required' => false,
            'Value' => '',
            'Filters' => array('Int'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limit_clean', array(
            'Label' => _('Время действия ограничения попыток'),
            'Required' => false,
            'Value' => '',
            'Filters' => array('Int'),
        ));

        $this->addElement($this->getDefaultTextElementName(), 'limit_time', array(
            'Label' => _('Ограничение по времени выполнения, мин.'),
            'Required' => false,
            'Value' => '',
            'Filters' => array('Int'),
        ));

        $elementIds = array(
            'mode_selection',
            'mode_selection_questions',
            'mode_selection_all_shuffle',
            'limit_attempts',   
            'limit_clean',
            'limit_time',
        );
        foreach ($clusters as $clusterId => $clusterName) {
            $elementIds[] = 'cluster_limit_'. $clusterId;
        };

        $this->addDisplayGroup(
            $elementIds,
            'quest_settings',
            array('legend' => _('Настройки теста'))
        );

        if (!empty($questId)) {
            /** @var HM_Quest_QuestService $questService */
            $questService = $this->getService('Quest');
            /** @var HM_Quest_QuestModel $quest */
            $quest = $questService->find($questId)->current();

            $lessonScope = HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON;
            $subjectScope = HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT;

            if ($quest->hasScopeSettings($lessonScope, $lessonId)) {
                $quest->setScope($lessonScope, $lessonId);
            } else {
                $quest->setScope($subjectScope, $subjectId);
            }

            $this->populate($quest->getSettings()->getData());
        }

    }

}