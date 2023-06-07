<?php

use HM_Role_Abstract_RoleModel as Roles;

class Lesson_ListController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    protected $_formName = 'HM_Form_Lesson';
    protected $_module = 'lesson';
    protected $_controller = 'list';

    protected $lesson;

    public function init()
    {
        parent::init();

        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $this->lesson = $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        $this->view->setHeader($lesson->title);

        $backUrl = [
            'module' => 'subject',
            'controller' => 'lessons',
            'action' => 'edit',
            'subject_id' => $this->lesson->CID,
        ];

        $this->view->setBackUrl($this->view->url($backUrl, null, true));
        $this->view->setSwitchContextUrls([]);
    }

    public function generateAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $i = 0;

        $questService = $this->getService('Quest');

        $quests = $questService->fetchAllDependenceJoinInner('SubjectAssign',
            $questService->quoteInto(
                array('SubjectAssign.subject_id = ?', ' AND self.type IN (?)'),
                array($subjectId, array(HM_Quest_QuestModel::TYPE_TEST, HM_Quest_QuestModel::TYPE_POLL))
            )
        );
        $questIds = $quests->getList('quest_id', 'title');

        foreach ($quests as $value) {
            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        //'params LIKE "%module_id=' . $test->tid . ';%" ',
                        'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            foreach ($lessons as $lesson) {
//                if (in_array($lesson->getModuleId(), array_keys($questIds))) {
                if ($lesson->getModuleId()==$value->quest_id) {
                    continue 2;
                }
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initTest();
            $form->populate(
                    array(
                        'title' => $value->name,//title
                        'event_id' => $value->type == HM_Quest_QuestModel::TYPE_TEST ? HM_Event_EventModel::TYPE_TEST : HM_Event_EventModel::TYPE_POLL,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->quest_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'threshold' => 0,
                        'students' => array(),
                        'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                        )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }

        // Электронные курсы
        $courses = $this->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($courses as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        'params LIKE ?' => '%module_id=' . $value->CID . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );

            if (count($lesson) > 0) {
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Lesson')->updateWhere(
                    array(
                'isfree' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED,
                    ), array(
                'CID = ?' => $subjectId,
                'params LIKE ?' => '%module_id=' . $value->CID . ';%',
                'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE,
                    )
            );

            $form = new $this->_formName();

            $form->getSubForm('step2')->initCourse();
            $form->populate(
                    array(
                        'title' => $value->Title,
                        'event_id' => HM_Event_EventModel::TYPE_COURSE,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->CID,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array(),
                        'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );

            $this->addLesson($form);
            unset($form);

            $i++;
        }


        // Ресурсы
        $resources = $this->getService('Resource')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($resources as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        "params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );

            if (count($lesson) > 0) {
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Lesson')->updateWhere(
                    array(
                'isfree' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED,
                    ), array(
                'CID = ?' => $subjectId,
                "params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE,
                    )
            );

            $form = new $this->_formName();
            $form->getSubForm('step2')->initResource();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_RESOURCE,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->resource_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array(),
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        //Опросы
        $polls = $this->getService('Poll')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($polls as $value) {

            $task = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->quiz_id));
            $taskId = $task->getList('tid', 'title');

            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        //'params LIKE "%module_id=' . $test->tid . ';%" ',
                        'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                    )
            );
            foreach ($lessons as $lesson) {
                if (in_array($lesson->getModuleId(), array_keys($taskId))) {
                    continue 2;
                }
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initPoll();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_POLL,
                        'vedomost' => 0,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->quiz_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        //webinars
        $webinars = $this->getService('Webinar')->fetchAll(array('subject_id = ?' => $subjectId));
        foreach ($webinars as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        'params LIKE ?' => '%module_id=' . $value->webinar_id . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_WEBINAR
                    )
            );
            if (count($lesson) > 0) {
                continue;
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initPoll();
            $form->populate(
                    array(
                        'title' => $value->name,
                        'event_id' => HM_Event_EventModel::TYPE_WEBINAR,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->webinar_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        //Tasks
        $tasks = $this->getService('Task')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($tasks as $value) {

            $task = $this->getService('Task')->fetchAll(array('task_id = ?' => $value->task_id));
            $taskId = $task->getList('task_id', 'title');

            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        'typeID = ?' => HM_Event_EventModel::TYPE_TASK
                    )
            );

            /** @var HM_Lesson_LessonModel $lesson */
            foreach ($lessons as $lesson) {
                if (in_array($lesson->getModuleId(), array_keys($taskId))) {
                    continue 2;
                }
            }

            $form = new $this->_formName();
            $form->getSubForm('step2')->initTest();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_TASK,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->task_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }

        $this->_flashMessenger->addMessage(sprintf(_('Сгенерировано занятий: %s'), $i));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
    }

    protected function addLesson($form)
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $activities = '';
        if (null !== $form->getValue('activities')) {
            if (is_array($form->getValue('activities')) && count($form->getValue('activities'))) {
                $activities = serialize($form->getValue('activities'));
            }
        }

        $tool = '';
        if ($form->getValue('event_id') < 0) {
            $event = $this->getOne(
                    $this->getService('Event')->find(-$form->getValue('event_id'))
            );
            if ($event) {
                $tool = $event->tool;
            }
        }

        $typeId = $form->getValue('event_id');

        /**
         * todo: Можно выкинуть? Вместе с использованием в
         * @see Lesson_ListController::_prepareParams
         * Или пока ещё обратнаясовместимость?
         */
        $moduleId = $form->getValue('module');
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE || $tool == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }
        $this->getService('Lesson')->setLessonFreeMode($moduleId, $typeId, $subjectId, HM_Lesson_LessonModel::MODE_FREE_BLOCKED);

        $data = array(
            'title' => $form->getValue('title'),
            'CID' => $subjectId,
            'typeID' => $form->getValue('event_id'),
            'vedomost' => (-$form->getValue('event_id')==HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY || -$form->getValue('event_id')==HM_Event_EventModel:: TYPE_OLYMPOX_EXAM)
                ? 1 : $form->getValue('vedomost'),
            'teacher' => $form->getValue('teacher'),
            'moderator' => $form->getValue('moderator'),
            'createID' => $this->getService('User')->getCurrentUserId(),
            'recommend' => $form->getValue('recommend'),
            'all' => (int) $form->getValue('all'),
            'notify_before' => $form->getValue('notify') ? $form->getValue('notify_before') : 0,
            'GroupDate' => $form->getValue('GroupDate'),
            'beginDate' => $form->getValue('beginDate'),
            'endDate' => $form->getValue('endDate'),
            'currentDate' => $form->getValue('currentDate'),
            'beginTime' => $form->getValue('beginTime'),
            'endTime' => $form->getValue('endTime'),
            'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
            'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
            'Condition' => $form->getValue('Condition'),
            'cond_sheid' => (string) $form->getValue('cond_sheid'),
            'cond_mark' => (string) $form->getValue('cond_mark'),
            'cond_progress' => (string) $form->getValue('cond_progress'),
            'cond_avgbal' => (string) $form->getValue('cond_avgbal'),
            'cond_sumbal' => (string) $form->getValue('cond_sumbal'),
            'has_proctoring' => (string) $form->getValue('has_proctoring'),
            'gid' => $form->getValue('subgroups'),
            'notice' => $form->getValue('notice'),
            'notice_days' => (int) $form->getValue('notice_days'),
            'activities' => $activities,
            'descript' => $form->getValue('descript'),
            'tool' => $tool,
            'threshold' => (string) $form->getValue('threshold') ? $form->getValue('threshold') : '0',
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
        );

        $lessons = $this->getService('Lesson')->fetchAll(array('CID = ?' => $subjectId));
        $lessonsOrders = $lessons->getList('order');
        if ($lessonsOrders) {
            $highestValue = max(array_values($lessonsOrders));
            $highestValue++;
            $data['order'] = $highestValue;
        }

        $lesson = $this->getService('Lesson')->insert($data);

        if ($lesson) {
            $this->_preProcessTest($lesson, $form);

            $students = $form->getValue('students');

            $groupId = $form->getValue('subgroups');
            $group = explode('_', $groupId);

            /* TODO Отписываем людей которые в ручнов выборе, если выбрана группа подгруппа? */
            if ($group[0] == 'sg' || $group[0] == 's') {
                $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
            }

            /* Параметр Учебная группа */
            if ($group[0] == 'sg') {
                $groupId = (int) $group[1];
                $students = $this->getService('StudyGroup')->getUsers($groupId);

                /* Добавляем запись что группа подписана на урок */
                // todo: Разница в получении студентов в addLesson и editDatesAction,
                // todo: например в этой строчке - преднамеренная? Собрать бы в один метод как
                /** @see Lesson_ListController::_prepareParams */
                $this->getService('StudyGroupCourse')->addLessonOnGroup($subjectId, $lesson->SHEID, $groupId);
            }

            /* Параметр Подгруппа */
            if ($group[0] == 's') {
                $groupId = (int) $group[1];
                if ($groupId > 0) {
                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                    $res = array();
                    foreach ($students as $value) {
                        $res[] = $value->mid;
                    }
                    $students = $res;
                }
            }

            if (!$form->getValue('switch')) {
                $students = $lesson->getService()->getAvailableStudents($subjectId);
            }

                /**
                 * тут что-то связано с вебинарами,
                 * принудительная запись модераторов и учителей в студенты - нарушает работу системы
                 * TODO: разобраться для чего это сделано и пофиксить по человечески
                 *
                if (is_array($students)) {
                    $students[] = $form->getValue('moderator');
                    $students[] = $form->getValue('teacher');
                    $students = array_unique($students);
                }
                else {
                    $students = array($form->getValue('moderator'), $form->getValue('teacher'));
                }
                 */

	        $formUserVariant = $form->getValue('user_variant');
	        $userVariants = array_filter(is_null($formUserVariant) ? array() : $formUserVariant);

            // Это круто кто-то закомментировал условие.....
            if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                $students = array_keys($userVariants);
            }

            $this->_postProcess($lesson, $form, $students);
            $lesson->setParams($this->_prepareParams($form, $lesson));
            $this->getService('Lesson')->update($lesson->getValues());


            if (is_array($students) && count($students)
                    && (($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                    || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL ))) {
                $this->assignStudents($lesson->SHEID, $students, $userVariants);
                $lessonAssignService = $this->getService('LessonAssign');
            }

            $this->getService('Subject')->update(array(
                'last_updated' => $this->getService('Subject')->getDateTime(),
                'subid' => $subjectId
            ));

	        if(!is_array($students)) {
		        $students == 0 ? array() : array($students);
	        }
            $this->_postProcess($lesson, $form, $students);
        }
        if($lesson) {//перечитываем, тк в постпроцессе, напр., вебинара, может ыть создаваемое занятие удалено
            $lesson = $this->getService('Lesson')->find($lesson->SHEID)->current();
        }

        return $lesson;
    }

    protected function assignStudents($lessonId, $students, $taskUserVariants = null)
    {

        if (is_array($students) && count($students)) {
            $this->getService('Lesson')->assignStudents($lessonId, $students, true, $taskUserVariants);
        } else {
            $this->getService('Lesson')->unassignAllStudents($lessonId);
        }
    }

    public function getFilterByRequest(Zend_Controller_Request_Http $request) {
        /* @var $factory Es_Service_Factory */
        $factory = $this->getService('ESFactory');
        /*@var $filter Es_Entity_AbstractFilter|Es_Entity_Filter */
        $filter = $factory->newFilter();
        $filter->setUserId((int) $this->getService('User')->getCurrentUserId());

        $eventGroup = $factory->eventGroup(
            HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, $request->getParam('subject_id')
        );

        $filter->setGroup($eventGroup);
        if ($eventGroup->getId() !== null) {
            $filter->setGroupId($eventGroup->getId());
        }
        if ($filter->getGroup()->getData() === null) {
            $data = array();
            $subject = $this->getOne($this->getService('Subject')->find($request->getParam('subject_id', 0)));
            if ($subject) {
                    $data = array(
                        'course_name' => $subject->name,
                        'course_id' => $subject->subid
                    );
            }
            $filter->getGroup()->setData(json_encode($data));
        }
        $eventTypeListPushEventResult = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_GET_TYPES_LIST,
            $this
        );
        $types = $eventTypeListPushEventResult->getReturnValue();
        $typesArr = array();
        $requiredTypes = array(
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ATTACH_LESSON,
            Es_Entity_AbstractEvent::EVENT_TYPE_LESSON_SCORE_TRIGGERED,
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_SCORE_TRIGGERED,
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_TASK_ACTION
        );
        foreach ($types as $eventType) {
            if (in_array($eventType->getId(), $requiredTypes)) {
                $typesArr[] = $eventType->getName();
            }
        }
        $filter->setTypes($typesArr);
        $filter->setEventId($request->getParam('eventId', null));

        return $filter;
    }

    public function indexAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $this->view->headLink()->appendStylesheet($this->view->serverUrl() . "/css/content-modules/schedule_table.css");

        $select = $this->getService('Lesson')->getSelect();
        $select->from(array('l' => 'lessons'), array(
            'lesson_id' => 'l.SHEID',
            'l.SHEID',
            'TypeID2' => 'l.typeID',
            'l.title',
            'l.typeID',
            'l.begin',
            'l.end',
            'l.timetype',
            'l.condition',
            'l.cond_sheid',
            'l.cond_mark',
            'l.cond_progress',
            'l.cond_avgbal',
            'l.cond_sumbal',
            'l.isfree',
            'l.has_proctoring',
            'sort_order' => 'l.order',
        ));
        $select->where('CID = ?', $subjectId)
                ->where('typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
                ->where('isfree = ?', HM_Lesson_LessonModel::MODE_PLAN)
                ->order(array('sort_order'));

        if ($this->getService('User')->getCurrentUserRole() != Roles::ROLE_ADMIN) {
            // нужно разобраться и потом раскомментировать
            // этот where() от вебинаров ломает всё расписание
            //$select->where("teacher = " . $this->getService('User')->getCurrentUserId() . ' OR ' . "moderator = " . $this->getService('User')->getCurrentUserId());
            //$select->where('teacher = ?', $this->getService('User')->getCurrentUserId());
        }

        $grid = $this->getGrid($select, array(
            'sort_order' => array('order' => true, 'hidden' => true),
            'SHEID' => array('hidden' => true),
            'has_proctoring' => array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            'typeID' => array('title' => _('Тип')),
            'begin' => array('title' => _('Ограничение по времени')),
            'condition' => array('title' => _('Условие')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
                ), array(
            'title' => null,
            'typeID' => array('values' => HM_Event_EventModel::getAllTypes(false)),
            'begin' => array('render' => 'DateTimeStamp'),
            'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие')))
        ));

        $grid->updateColumn('typeID', array('searchType' => '='));
        $grid->addAction(
            array(
                'module' => 'lesson',
                'controller' => 'list',
                'action' => 'proctored',
            ),
            array('lesson_id'),
            _('Контролировать прохождение')
        );

        $grid->setActionsCallback(
                array('function' => array($this, 'updateActions'),
                    'params' => array('{{TypeID2}}', '{{has_proctoring}}')
                )
        );

        $grid->addAction(array(
                'module' => 'subject',
                'controller' => 'lesson',
                'action' => 'edit',
                'subject_id' => $subjectId,
            ),
            array('lesson_id'),
            _('Редактировать карточку')
        );

        $grid->addAction(array(
                'module' => 'subject',
                'controller' => 'lesson',
                'action' => 'edit-assign',
                'subject_id' => $subjectId,
            ),
            array('lesson_id'),
            _('Назначить участников')
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'lesson',
            'action' => 'edit-material',
            'subject_id' => $subjectId,
        ),
            array('lesson_id'),
            $this->view->svgIcon('editContent', _('Редактировать содержимое'))
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'delete'
            ), array('lesson_id'), $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(
            array(
                'module' => 'lesson',
                'controller' => 'result',
                'action' => 'index',
                'preview' => 1
            ),
            array('lesson_id'),
            _('Просмотр результатов')
        );



        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных занятий? Если занятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем слушателям курса в меню <Материалы курса>.'));

        $grid->updateColumn('typeID', array(
            'callback' =>
            array(
                'function' => array($this, 'getTypeString'),
                'params' => array('{{typeID}}')
            )
                )
        );

        $grid->updateColumn('begin', array(
            'callback' =>
            array(
                'function' => array($this, 'getDateTimeString'),
                'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
            )
        ));

        $grid->updateColumn('title', array(
            'callback' =>
            array(
                'function' => array($this, 'updateName'),
                'params' => array('{{title}}', '{{lesson_id}}', '{{typeID}}')
            )
        ));

        $grid->updateColumn('condition', array(
            'callback' =>
            array(
                'function' => array($this, 'getConditionString'),
                'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
            )
        ));

        $grid->addMassAction(
            array(
                'module' => 'lesson',
                'controller' => 'list',
                'action' => 'export-variants',
            ),
            _('Cгенерировать варианты теста')
        );
        $grid->addSubMassActionInput(
            array($this->view->url(array('action' => 'export-variants'))),
            'variant_count'
        );

// exit($select->__toString());

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
//[ES!!!] //array('filter' => $this->_getFilterByRequest($this->getRequest()))
    }

    public function updateActions($typeID, $hasProctoring, $actions) {
        $lesson = HM_Lesson_LessonModel::factory(array('typeID' => $typeID));
        $result = explode('<li>', $actions);

        if(!empty($lesson) && !$lesson->isResultInTable()) {
            unset($result[1]);
        }

        if(!$hasProctoring) {
            unset($result[2]);
        }

        return implode('<li>', $result);
    }

    public function updateActions2($SSID, $fileId, $actions) {

        // Просмотр фото
        if(!$fileId) {
            $this->unsetAction($actions, [
                'module' => 'file',
                'controller' => 'get',
                'action' => 'file'
            ]);
        }

        $select = $this->getService('ProctoringFile')->getSelect()
            ->from(
                array('p' => 'proctoring_files')
            )
            ->where('SSID=?', $SSID)
            ->order('proctoring_file_id');

        $rows = $select->query()->fetchAll();

        foreach($rows as $row)  {
            $typeCaption = '';

            switch ($row['type']) {
                case HM_Proctoring_File_FileModel::TYPE_CAMERA:
                    $typeCaption = _('Скачать видеозапись камеры');
                    break;
                case HM_Proctoring_File_FileModel::TYPE_SCREEN:
                    $typeCaption = _('Скачать видеозапись экрана');
                    break;
            }

            $fileCaption = join(
                ' #', array_filter(array(
                    $typeCaption,
                    ceil(++$i/2)
                ))
            );

            $actions[] = array('url' => $row['url'], 'icon' => $fileCaption);
        }
        return $actions;
    }

    public function videoAction()
    {
        $userService = $this->getService('User');
        $select = $userService->getSelect()
            ->from(
                array('scid' => 'scheduleID'),
                array('scid.remote_event_id', 'scid.MID')
            )
            ->joinInner(array('sc' => 'schedule'), 'scid.SHEID=sc.SHEID and sc.has_proctoring=1', array())
            ->where('scid.SHEID = ?', $this->lesson->SHEID)
            ->where('scid.remote_event_id IS NOT NULL')
            ->where('scid.MID <> 0')
            ->where('scid.passed_proctoring = 0');

        $events = $select->query()->fetchAll();
        $config = Zend_Registry::get('config');
        $data = array();
        foreach($events as $event) {
            $userId = $event['MID'];
            $remoteEventId = $event['remote_event_id'];
            $data = array(
                'user_id' => $event['MID'],
                'teacher_id' => $this->getService('User')->getCurrentUserId(),
                'event_id' => $remoteEventId,
                'app_key' => $config->proctoring->appKey,
                'els_role' => HM_Proctoring_ProctoringService::ELS_ROLE_STUDENT,
                'url' => $this->getService('Proctoring')->getEventUrl(
//                    $remoteEventId,
                    $this->lesson->SHEID,
                    HM_Proctoring_ProctoringService::ELS_ROLE_STUDENT, //HM_Proctoring_ProctoringService::ELS_ROLE_TEACHER,
                    $userId
                )
            );
        }

        $this->view->data = $data;
    }

    public function proctoredAction()
    {
        $this->view->setSubSubHeader("Контроль прохождения занятия");

        $lessonAssignService = $this->getService('LessonAssign');

        $default = Zend_Registry::get('session_namespace_default');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $page = sprintf('%s-%s-%s', $request->getModuleName(), $request->getControllerName(), $request->getActionName());

        if (isset($default->grid[$page]['grid'])) {
            if (is_array($default->grid[$page]['grid']['filters']) && count($default->grid[$page]['grid']['filters'])) {
                unset($default->grid[$page]['grid']['filters']['lesson_id']);
            }
        }


//        $this->view->imThatTeacher = $this->getService('User')->getCurrentUserId()==$lesson->teacher;

        $select = $lessonAssignService->getSelect()->distinct()
            ->from(
                array('p' => 'People'),
                array(
                    'MID',
                    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'online' => new Zend_Db_Expr('1'),
                    //'auth_proctoring' => 'sc.auth_proctoring',
                    'passed_proctoring' => 'sc.passed_proctoring',
                    'video_proctoring' => 'sc.video_proctoring',
                    'lesson_id' => 'sc.SHEID',
                    'sc.remote_event_id',
                    'SSID'=>'sc.SSID',
                    'watch' => new Zend_Db_Expr('1'),
                    'file_id' => 'sc.file_id'
                )
            )
            ->joinInner(
                array('sc' => 'scheduleID'),
                $lessonAssignService->quoteInto(array('sc.MID=p.MID and sc.SHEID=?'), array($this->lesson->SHEID)),
                array()
            )
            ->joinInner(
                array('s' => 'schedule'),
                $lessonAssignService->quoteInto(
                    array('sc.SHEID=s.SHEID and s.has_proctoring = ?'), array(1)
                ),
                array()
            )
            ->joinInner(array('st' => 'Students'), 'st.MID=p.MID', array());

        $grid = $this->getGrid(
            $select,
            array(
                'MID' => array('hidden' => true),
                'SSID' => array('hidden' => true),
                'lesson_id' => array('hidden' => true),
                'remote_event_id' => array('hidden' => true),
                'fio' => array(
                    'title' => _('ФИО'),
                    'callback' => array(
                        'function' => array($this, 'updateFioAndWatch'),
                        'params' => array('{{fio}}', '{{MID}}', '{{lesson_id}}')
                    ),
                ),
                'online' => array(
                    'title' => _('Онлайн'),
                    'callback' => array(
                        'function' => array($this, 'updateOnlineColumn'),
                        'params' => array('{{MID}}', '{{lesson_id}}')//remote_event_id
                    ),
                ),
                'passed_proctoring' => array(
                    'title' => _('Допущен к занятию'),
                    'callback' => array(
                        'function' => array($this, 'updateBoolColumn'),
                        'params' => array('{{passed_proctoring}}')
                    ),
                    'type' => 'boolean'
                ),
                'video_proctoring' => array(
                    'title' => _('Ведётся запись'),
                    'callback' => array(
                        'function' => array($this, 'updateBoolColumn'),
                        'params' => array('{{video_proctoring}}')
                    ),
                    'type' => 'boolean'
                ),
                /*'auth_proctoring' => array(
                    'title' => _('Допущен'),
                    'callback' => array(
                        'function' => array($this, 'updateBoolColumn'),
                        'params' => array('{{auth_proctoring}}')
                    ),
                    'type' => 'boolean'
                ),*/
                'watch' => array('hidden' => true),
//                array(
//                    'title' => _('Прокторинг'),
//                    'callback' => array(
//                        'function' => array($this, 'updateWatchColumn'),
//                        'params' => array(
//                            '{{lesson_id}}',
//                            '{{MID}}',
//                            '{{SSID}}'
//                        )
//                    ),
//                ),
                'file_id' => array('hidden' => true)
            ),
            array(
                'fio' => null,
                'online' => array(
                    'values' => $this->getBoolColumnFilter(),
                    'callback' => array('function' => array($this, 'filterOnline')),
                ),
                'passed_proctoring' => array('values' => $this->getBoolColumnFilter()),
                'auth_proctoring' => array('values' => $this->getBoolColumnFilter()),
                'video_proctoring' => array('values' => $this->getBoolColumnFilter()),
            )
        );

        $grid->addAction(
            array('module' => 'lesson', 'controller' => 'list', 'action' => 'validate-proctoring', 'passed' => 1),
            array('MID'),
            _('Изменить статус: допущен')
        );

        $grid->addAction(
            array('module' => 'lesson', 'controller' => 'list', 'action' => 'validate-proctoring', 'passed' => 0),
            array('MID'),
            _('Изменить статус: не допущен')
        );

        $grid->addAction(
            array(
                'module' => 'lesson',
                'controller' => 'player',
                'action' => 'video-player'
            ),
            array('MID'),
            _('Просмотреть видеозаписи'),
            null,
            '_blank'
        );

        $grid->addAction(
            array(
                'module' => 'file',
                'controller' => 'get',
                'action' => 'file'
            ),
            array('file_id'),
            _('Скачать фото')
        );

        $grid->addMassAction(
            array('module' => 'lesson', 'controller' => 'list', 'action' => 'validate-proctoring', 'passed' => 0),
            _('Изменить статус: не допущен')
        );

        $grid->addMassAction(
            array('module' => 'lesson', 'controller' => 'list', 'action' => 'validate-proctoring', 'passed' => 1),
            _('Изменить статус: допущен')
        );

        $grid->setActionsCallback(
                array('function' => array($this, 'updateActions2'),
                    'params' => array('{{SSID}}', '{{file_id}}')
                )
        );

        if ($this->lesson->teacher == $this->getService('User')->getCurrentUserId()){
            $grid->addMassAction(
                array('module' => 'lesson', 'controller' => 'list', 'action' => 'mass-watch-proctoring'),
                _('Контролировать одновременно')
            );
        }

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function filterOnline($data)
    {
        $select = $data['select'];
        $isBroadcast = $data['value'];

        $usersIds = array_merge($this->getService('Proctoring')->getUserIdsByBroadcast($this->lesson->SHEID, $isBroadcast), array(0));

        $select->where($this->getService('User')->quoteInto(
            array('p.MID in (?)'),
            array($usersIds)
        ));
    }

    public function massWatchProctoringAction()
    {
        $this->initPrint();

        $massIds = $this->_getParam('postMassIds_grid', null);
        $massIds = implode(',', array_slice(explode(',', $massIds), 0 ,12));

        $currentUserId = $this->getService('User')->getCurrentUserId();
        $lessonAssign = $this->getService('LessonAssign')->fetchRow(array('remote_event_id is not null and remote_event_id <> 0 and SHEID = ?' => $this->lesson->SHEID));
        $lesson = $this->getService('Lesson')->fetchRow(array('SHEID = ?' => $this->lesson->SHEID));
        if($lesson) {
            $this->view->title = $lesson->title;
        }

        $this->view->url = $this->getService('Proctoring')->getEventUrl(
//            $lessonAssign ? $lessonAssign->remote_event_id : '',
            $this->lesson->SHEID,
            HM_Proctoring_ProctoringService::ELS_ROLE_TEACHER,
            $currentUserId,
            null,
            $massIds
        );
    }

    public function updateOnlineColumn($MID, $lessonId)//$remoteEventId)
    {
        $isBroadCasting = $this->getService('Proctoring')->isBroadcasting($MID, $lessonId);

        if($isBroadCasting) {
            return _('Да');
        }
        else {
            return _('Нет');
        }
    }

//    public function updateWatchColumn($remoteEventId, $userId /*, $SSID*/)
    public function updateFioAndWatch($fio, $userId, $lessonId)
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();

        $lesson = $this->getService('Lesson')->find($lessonId)->current();
        $teacher = $this->getService('User')->findOne($lesson->teacher);

        if (!$teacher) return $fio;
        elseif ($teacher->MID != $currentUserId) {
            return sprintf('<span title="Контроль прохождения занятия доступен пользователю %s; другого ответственного можно указать на странице назначения участников занатия.">%s</span>',
                $teacher->getName(),
                $fio
            );
        }

        /** proctoring url для преподавателя */
        $url = $this->getService('Proctoring')->getEventUrl(
            $lessonId,
            HM_Proctoring_ProctoringService::ELS_ROLE_TEACHER,
            $currentUserId,
            $userId             // privateWithUserId
        );

        return "<hm-proctoring-teacher-activator url=\"{$url}\" name=\"{$fio}\"></hm-proctoring-teacher-activator>";
    }

    public function validateProctoringAction()
    {
        $passed = $this->_getParam('passed', null);

        if(!is_null($passed)) {
            $usersIds = $this->_getParam('MID', 0);

            if(empty($usersIds)) {
                $massIds = $this->_getParam('postMassIds_grid', null);
                $usersIds = array_filter(explode(',', $massIds));
            }

            foreach((array) $usersIds as $userId) {
                $this->getService('LessonAssign')->updateWhere(
                    array('passed_proctoring' => $passed),
                    array('MID = ?' => $userId, 'SHEID = ?' => $this->lesson->SHEID)
                );
            }
        }

        $subjectId = $this->_getParam('subject_id', 0);
        $this->_redirector->gotoSimple(
            'proctored',
            $this->_controller,
            $this->_module,
            array('subject_id' => $subjectId, 'lesson_id' => $this->lesson->SHEID)
        );
    }

    public function saveOrderAction() {
        $this->getHelper('viewRenderer')->setNoRender();
        $order = $this->_getParam('posById', array());
        foreach ($order as $key => $lesson) {
            $res = $this->getService('Lesson')->updateWhere(array('order' => $key), array('SHEID = ?' => $lesson));
            if ($res === false) {
                echo Zend_Json_Encoder::encode(array('result' => false));
                exit;
            }
        }
        echo Zend_Json_Encoder::encode(array('result' => true));
    }

    public function getConditionString($condSheid, $condMark, $condProgress, $condAvg, $condSum) {
        $conditions = HM_Lesson_LessonModel::getConditionTypes();
        if ($condSheid > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_LESSON];
        }
        if ($condProgress > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_PROGRESS];
        }
        if ($condAvg > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_AVGBAL];
        }
        if ($condSum > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_SUMBAL];
        }
        return _('Нет');
    }

    public function getTypeString($typeId)
    {
        $types = HM_Event_EventModel::getAllTypes();
        if (isset($types[$typeId])) {
            return $types[$typeId];
        }
    }

    public function getDateTimeString($begin, $end, $timetype)
    {
        switch ($timetype) {
            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                if (($end == 0) || ($begin == 0)) {
                    $beginOrEnd = ($begin == 0) ? $end : $begin;
                    return sprintf(_('%s-й день'), floor($beginOrEnd / 60 / 60 / 24));
                } elseif ($begin != $end) {
                    return sprintf(_('%s-й день - %s-й день'), floor($begin / 60 / 60 / 24), floor($end / 60 / 60 / 24));
                } else {
                    return sprintf(_('%s-й день'), floor($begin / 60 / 60 / 24));
                }
                break;
            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                return _('Без ограничений');
                break;
            default:
                try {
                    $begin = new HM_Date($begin);
                } catch (Exception $e) {
                    $begin = new HM_Date($begin);
                }

                try {
                    $end = new HM_Date($end);
                } catch (Exception $e) {
                    $end = new HM_Date($end);
                }

                return sprintf('%s - %s', $begin->get(Zend_Date::DATETIME_SHORT), $end->get(Zend_Date::DATETIME_SHORT));
                break;
        }
    }

    public function myAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $student = 0;

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');
        /** @var HM_Unmanaged_UnmanagedService $unmanagedService */
        $unmanagedService = $this->getService('Unmanaged');
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        /** @var HM_Subject_Mark_MarkService $subjectMarkService */
        $subjectMarkService = $this->getService('SubjectMark');

        $currentUserId = $userService->getCurrentUserId();
        $currentUserRole = $userService->getCurrentUserRole(true);

        $currentUserIsEndUser = $acl->inheritsRole($currentUserRole, Roles::ROLE_ENDUSER);
        $currentUserIsTeacher = $acl->inheritsRole($currentUserRole, Roles::ROLE_TEACHER);

        $view = $this->view;

        if ($currentUserIsEndUser) {
            $student = $currentUserId;
            $view->setHeaderOptions(array(
                        'pageTitle' => _('План занятий'),
                'panelTitle' => $view->getPanelShortname(array(
                                    'subject' => $this->_subject,
                                    'subjectName' => 'subject',
                )),
            ));
        }

        if ($currentUserIsTeacher) {

            $student = $this->_getParam('user_id', false);
            $user = $this->getOne($userService->find($student));

            if ($student && $user) {
                $unmanagedService->setSubHeader($user->getName());
            }
        }

        if ($student) {
            $subSelect = $lessonService->getSelect();
            $subSelect->from(array('ul' => 'scheduleID'), 'SHEID')
                    ->where('MID = ?', $student);
            $addingWhere = array('SHEID IN ?' => $subSelect);
        } else {
            $addingWhere = array();
        }

        $lessons = $lessonService->fetchAllDependence(
                array('Assign', 'Teacher'), array(
            'CID = ?' => $subjectId,
            'typeID NOT IN (?)' => array_keys(
                    HM_Event_EventModel::getExcludedTypes()
            ),
            'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                ) + $addingWhere, array('order')
        );


        $titles = $lessons->getList('SHEID', 'title');
        $percent = $lessonService->countPercents($lessons, $student);
        $collection = $subjectMarkService->fetchAll(
            $subjectMarkService->quoteInto(
                array('cid = ?', ' AND mid = ?'),
                array(
                    $subjectId,
                    $student,
                        )
                )
        );
        $view->mark = count($collection) ? $this->getOne($collection)->mark : HM_Scale_Value_ValueModel::VALUE_NA;

        $view->titles = $titles;
        $view->markDisplay = (boolean) $student;
        $view->percent = (int) $percent;
        $view->lessons = $lessons;
        $view->subject = $this->_subject;

//        @todo: Сделать нормальный view и отображать с делением на sections
//        $titles = array();
//        $this->view->sections = $this->getService('Section')->getSectionsLessons($subjectId, $addingWhere, $titles);
//        $this->view->titles = $titles;
//        $this->view->isEditSectionAllowed = $this->getService('Acl')->isCurrentAllowed('mca:lesson:list:edit-section');

        $view->forStudent = $student;
        $view->isStudentRole = $currentUserIsEndUser;
        $view->currentUserIsTeacher = $currentUserIsTeacher;

        if ($acl->inheritsRole($currentUserRole, array(Roles::ROLE_TEACHER, Roles::ROLE_DEAN))) {
            $view->assign(array(
                'lessonView' => 'lesson-preview-teacher',
                'lessonCols' => array(
                    '88px',
                    'auto',
                    '110px',
                    '256px',
                ),
                'headerCols' => array(
                    'auto',
                    '366px',
                )
            ));
        } else {
            $view->assign(array(
                'lessonView' => 'lesson-preview',
                'lessonCols' => array(
                    '88px',
                    'auto',
                    '110px',
                    '250px',
                ),
                'headerCols' => array(
                    'auto',
                    '360px',
                )
            ));
        }

        /* +++ Events +++ */
        /*@var $filter Es_Entity_Filter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int) $currentUserId);
        $filter->setTypes(array(
            'courseAttachLesson',
            'courseTaskScoreTriggered'
        ));
        $filter->setIsGroupResultRequire(false);
        $filter->setOnlyNotShowed(false);
        $filter->setFromTime($filter->getToTime() - 5*86400);

        $group = $this->getService('ESFactory')->eventGroup(
            HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, $subjectId
        );
        $group->setData(json_encode(array(
            'course_name' => $this->_subject->name
        )));
        $filter->setGroup($group);

        $event = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_PULL,
            $this,
            array('filter' => $filter)
        );
        $eventCollection = $event->getReturnValue();
        $view->eventCollection = $eventCollection;
        /* ---Events --- */

        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        );

    }

    public function newAction()
    {
        if (isset($_POST['questions_by_theme'])) {
            if (is_array($_POST['questions_by_theme']) && count($_POST['questions_by_theme'])) {
                $_POST['questions_by_theme'] = serialize($_POST['questions_by_theme']);
            }
        }

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $request = $this->getRequest();

        /** @var HM_Form_Lesson $form */
        $form = new $this->_formName();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $checkResult = $this->checkLessonDates($form, $subjectId);
            $lesson = $this->addLesson($form);
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->subjectId = $form->getValue('subject_id');
                return true;
            } else {
                $extraMsg = (in_array($form->getValue('event_id'), HM_Lesson_LessonModel::getTypesFreeModeEnabled())) ? _('Закрыт свободный доступ к материалы курса, использованным в данном занятии') : '';
                $this->_flashMessenger->addMessage(_('Занятие успешно добавлено. ') . $extraMsg);

                $lessonId = $lesson->SHEID;
                if($lessonId) {
                    $proctoringFailExport = $this->getService('Proctoring')->hasFailExport($lessonId);
                    if($proctoringFailExport) {
                        $this->getService('Lesson')->updateWhere(
                            array('has_proctoring' => 0),
                            array('SHEID = ?' => $lessonId)
                        );

                        $this->_flashMessenger->addMessage(array(
                            'message' => _('Ошибка передачи данных в сервис прокторинга. Флаг "требуется аутентификация" был выключен.'),
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR
                        ));
                    }
                }

                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                }
                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
            }
        } else {
            if ($questId = $this->_getParam('quest_id', 0)) {
                $form->setDefault('module', $questId);
            }
            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('GroupDate', HM_Lesson_LessonModel::TIMETYPE_FREE);
        }

        $this->view->form = $form;
        $this->view->subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
    }

    public function editAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson) {
            $this->getService('Unmanaged')->setSubHeader($lesson->title);
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Занятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
        }

        $form = new $this->_formName();
        $params = $lesson->getParams();

        if ($this->_getParam('fromlist', false)) {
            $form->getSubForm('step1')
                ->getElement('cancelUrl')
                ->setValue($this->view->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $this->_getParam('subject_id', 0), 'user_id' => intval($this->_getParam('user_id', null))), null, true) . '#lesson_' . $lessonId);
            if ($this->_getParam('user_id', false)) {
                $form->getSubForm('step1')
                    ->getElement('fromList')
                    ->setValue(intval($this->_getParam('user_id')));
            } else {
                $form->getSubForm('step1')
                    ->getElement('fromList')
                    ->setValue('y');
            }
        }

        if (isset($_POST['questions_by_theme'])) {
            if (is_array($_POST['questions_by_theme']) && count($_POST['questions_by_theme'])) {
                $_POST['questions_by_theme'] = serialize($_POST['questions_by_theme']);
            }
        }

        $request = $this->getRequest();
        $formValues = $request->getPost();

        if ($request->isPost() && $form->isValid($formValues)) {

            $activities = '';
            if (null !== $form->getValue('activities')) {
                if (is_array($form->getValue('activities')) && count($form->getValue('activities'))) {
                    $activities = serialize($form->getValue('activities'));
                }
            }
            $checkResult = $this->checkLessonDates($form, $subjectId);

            $tool = '';
            if ($form->getValue('event_id') < 0) {
                $event = $this->getOne(
                    $this->getService('Event')->find(-$form->getValue('event_id'))
                );

                if ($event) {
                    $tool = $event->tool;
                }
            }

            $typeId = $form->getValue('event_id');
            $moduleId = $form->getValue('module');
            if ($typeId == HM_Event_EventModel::TYPE_LECTURE || $tool == HM_Event_EventModel::TYPE_LECTURE) {
                $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
                $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
            }
            $this->getService('Lesson')->setLessonFreeMode($moduleId, $typeId, $subjectId, HM_Lesson_LessonModel::MODE_FREE_BLOCKED);

            $lesson = $this->getService('Lesson')->update(
                array(
                    'SHEID' => $form->getValue('lesson_id'),
                    'title' => $form->getValue('title'),
                    'CID' => $form->getValue('subject_id'),
                    'typeID' => $form->getValue('event_id'),
                    'vedomost' => $form->getValue('vedomost'),
                    'teacher' => $form->getValue('teacher'),
                    'moderator' => $form->getValue('moderator'),
                    'createID' => $this->getService('User')->getCurrentUserId(),
                    'recommend' => $form->getValue('recommend'),
                    'all' => $form->getValue('all'),
                    'GroupDate' => $form->getValue('GroupDate'),
                    'beginDate' => $form->getValue('beginDate'),
                    'has_proctoring' => $form->getValue('has_proctoring'),
                    'endDate' => $form->getValue('endDate'),
                    'currentDate' => $form->getValue('currentDate'),
                    'beginTime' => $form->getValue('beginTime'),
                    'endTime' => $form->getValue('endTime'),
                    'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
                    'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
                    'Condition' => $form->getValue('Condition'),
                    'cond_sheid' => $form->getValue('cond_sheid'),
                    'cond_mark' => ((null !== $form->getValue('cond_mark')) ? $form->getValue('cond_mark') : ''),
                    'cond_progress' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_progress') : 0),
                    'cond_avgbal' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_avgbal') : 0),
                    'cond_sumbal' => ((null !== $form->getValue('cond_sumbal')) ? $form->getValue('cond_sumbal') : 0),
                    'gid' => $form->getValue('subgroups'),
                    'notice' => $form->getValue('notice'),
                    'notice_days' => (int) $form->getValue('notice_days'),
                    'activities' => $activities,
                    'descript' => $form->getValue('descript'),
                    'tool' => $tool,
                    'threshold' => ((null !== $form->getValue('threshold')) ? $form->getValue('threshold') : 0),
                )
            );

            if ($lesson) {

                if ($form->getValue('module')) {
                    $params['module_id'] = $form->getValue('module');
                }

                if ($form->getValue('assign_type')) {
                    $params['assign_type'] = $form->getValue('assign_type');
                } elseif (isset($params['assign_type']) && $params['assign_type']) {
                    unset($params['assign_type']);
                }

                if ($form->getValue('is_hidden', 0)) {
                    $params['is_hidden'] = $form->getValue('is_hidden');
                } elseif (isset($params['is_hidden']) && $params['is_hidden']) {
                    unset($params['is_hidden']);
                }

                if ($form->getValue('formula')) {
                    $params['formula_id'] = $form->getValue('formula');
                } elseif (isset($params['formula_id'])) {
                    unset($params['formula_id']);
                }

                if ($form->getValue('formula_group')) {
                    $params['formula_group_id'] = $form->getValue('formula_group');
                }

                if ($form->getValue('formula_penalty')) {
                    $params['formula_penalty_id'] = $form->getValue('formula_penalty');
                }

                if ($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) {
                    $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
                }

                $lesson->setParams($params);

                $this->getService('Lesson')->update($lesson->getValues());

                $students = $form->getValue('students');

                $groupId = $form->getValue('subgroups');

                //**//
                $group = explode('_', $groupId);

                /* TODO Отписываем людей которые в ручнов выборе, если выбрана группа подгруппа? Помоему туда постоянно поподает 0 */
                if ($group[0] == 'sg' || $group[0] == 's') {
                    $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
                    $this->getService('StudyGroupCourse')->removeLessonFromGroups($subjectId, $lesson->SHEID);
                }
                /* Параметр Учебная группа */
                if ($group[0] == 'sg') {
                    $groupId = (int) $group[1];
                    $students = $this->getService('StudyGroup')->getUsers($groupId);
                    /* Добавляем запись что группа подписана на урок */
                    $this->getService('StudyGroupCourse')->addLessonOnGroup($subjectId, $lesson->SHEID, $groupId);
                }
                /* Параметр Подгруппа */
                if ($group[0] == 's') {
                    $groupId = (int) $group[1];
                    if ($groupId > 0) {
                        $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                        $res = array();
                        foreach ($students as $value) {
                            $res[] = $value->mid;
                        }
                        $students = $res;
                    }
                }

                //**//
//
//                if($groupId > 0){
//                    $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
//
//                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));
//
//                    $res = array();
//                    foreach($students as $value){
//                        $res[] = $value->mid;
//                    }
//                    $students = $res;
//                }
//


                if (!$form->getValue('switch')) {
                    $students = $lesson->getService()->getAvailableStudents($subjectId);
                }

                /**
                 * тут что-то связано с вебинарами,
                 * принудительная запись модераторов и учителей в студенты - нарушает работу системы
                 * TODO: разобраться для чего это сделано и пофиксить по человечески
                 *
                if (is_array($students)) {
                if ($form->getValue('moderator') !== null)
                $students[] = $form->getValue('moderator');
                $students[] = $form->getValue('teacher');
                $students = array_unique($students);
                }
                else {
                $students = array($form->getValue('moderator'), $form->getValue('teacher'));
                }
                 */

                $formUserVariant = $form->getValue('user_variant');
                $userVariants = array_filter(is_null($formUserVariant) ? array() : $formUserVariant);

                if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                    $students = array_keys($userVariants);
                }

                //$this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                if ((($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                    || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL ))) {
                    $this->assignStudents($lesson->SHEID, $students, $userVariants);
                }

                if(!is_array($students)) {
                    $students = $students == 0 ? array() : array($students);
                }
                $this->_postProcess($lesson, $form, $students);
            }
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->cancelUrl = $form->getValue('cancelUrl');
                return true;
            } else {
                $this->_flashMessenger->addMessage(_('Занятие успешно изменено'));
                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                }

                $proctoringFailExport = $this->getService('Proctoring')->hasFailExport($lessonId);
                if($proctoringFailExport) {
                    $this->getService('Lesson')->updateWhere(
                        array('has_proctoring' => 0),
                        array('SHEID = ?' => $lessonId)
                    );

                    $this->_flashMessenger->addMessage(array(
                        'message' => _('Ошибка передачи данных в сервис прокторинга. Флаг "требуется аутентификация" был выключен.'),
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR
                    ));
                }

                if ($form->getValue('fromList')) {
                    $url = array(
                        'module' => 'lesson',
                        'controller' => 'list',
                        'action' => 'my',
                        'subject_id' => $lesson->CID
                    );
                    if ($form->getValue('fromList') == strval(intval($form->getValue('fromList')))) {
                        $url['user_id'] = $form->getValue('fromList');
                    }
                    $this->_redirector->gotoUrl($this->view->url($url, null, true) . '#lesson_' . $lessonId);
                }

                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $lesson->CID));
            }
        } else {
            if ($lessonId) {
                if ($lesson) {
                    $values = array(
                        'lesson_id' => $lesson->SHEID,
                        'title' => $lesson->title,
                        'subject_id' => $lesson->CID,
                        'event_id' => $lesson->typeID,
                        'vedomost' => $lesson->vedomost,
                        'teacher' => $lesson->teacher,
                        'moderator' => $lesson->moderator,
                        'recommend' => $lesson->recommend,
                        'all' => $lesson->all,
                        'module' => $lesson->getModuleId(),
                        'formula' => $lesson->getFormulaId(),
                        'formula_group' => $lesson->getFormulaGroupId(),
                        'formula_penalty' => $lesson->getFormulaPenaltyId(),
                        'cond_sheid' => $lesson->cond_sheid,
                        'cond_mark' => $lesson->cond_mark,
                        'cond_progress' => $lesson->cond_progress,
                        'cond_avgbal' => $lesson->cond_avgbal,
                        'cond_sumbal' => $lesson->cond_sumbal,
                        'gid' => $lesson->gid,
                        'notice' => $lesson->notice,
                        'notice_days' => $lesson->notice_days,
                        'descript' => $lesson->descript,
                        'assign_type' => (isset($params['assign_type'])) ? (int) $params['assign_type'] : HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM,
                        'section_id' => $lesson->section_id,
                        'threshold' => $lesson->threshold,
                        'has_proctoring' => $lesson->has_proctoring,
                    );

                    if ($lesson->activities && strlen($lesson->activities)) {
                        $values['activities'] = unserialize($lesson->activities);
                    }

                    if ($lesson->cond_sheid) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_LESSON;
                    }

                    if ($lesson->cond_progress) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_PROGRESS;
                    }

                    if ($lesson->cond_avgbal) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_AVGBAL;
                    }

                    if ($lesson->cond_sumbal) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_SUMBAL;
                    }

                    if ($lesson->gid != 0 && $lesson->gid != -1) {
                        $values['switch'] = 2;
                    } else {
                        $values['switch'] = 1;
                    }

                    switch ($lesson->timetype) {
                        case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_RELATIVE;
                            $values['beginRelative'] = floor($lesson->startday / 24 / 60 / 60);
                            $values['endRelative'] = floor($lesson->stopday / 24 / 60 / 60);
                            break;
                        case HM_Lesson_LessonModel::TIMETYPE_FREE:
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                            break;
                        default:
                            $values['beginDate'] = $lesson->getBeginDate();
                            $values['endDate'] = $lesson->getEndDate();
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_DATES;
                            if ($values['beginDate'] == $values['endDate']) {
                                $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_TIMES;
                                $values['currentDate'] = $values['beginDate'];
                                $values['beginTime'] = $lesson->getBeginTime();
                                $values['endTime'] = $lesson->getEndTime();
                                unset($values['beginDate']);
                                unset($values['endDate']);
                            }
                            break;
                    }

                    switch ($lesson->getType()) {
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                        case HM_Event_EventModel::TYPE_TASK:
                        case HM_Event_EventModel::TYPE_POLL:
//@D                        case HM_Event_EventModel::TYPE_TEST:
                            $test = $this->getOne($this->getService('Test')->fetchAll(
                                $this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID)
                            ));
                            if ($test) {
                                // Набить форму данными теста
                                $values['mode'] = $test->mode;
                                $values['lim'] = $test->lim;
                                $values['qty'] = $test->qty;
                                $values['startlimit'] = $test->startlimit;
                                $values['limitclean'] = $test->limitclean;
                                $values['timelimit'] = $test->timelimit;
                                $values['random'] = $test->random;
                                //$values['adaptive'] = $test->adaptive;
                                $values['questres'] = $test->questres;
                                $values['showurl'] = $test->showurl;
                                $values['endres'] = $test->endres;
                                $values['skip'] = $test->skip;
                                $values['allow_view_url'] = $test->allow_view_url;
                                $values['allow_view_log'] = $test->allow_view_log;
                                $values['comments'] = $test->comments;
                                $values['module'] = $test->test_id;

//                                 $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
//                                                 $this->getService('TestTheme')->quoteInto(
//                                                         array('tid = ?', ' AND cid = ?'), array($test->tid, $test->cid)
//                                                 )
//                                         ));

                                if ($theme && count($theme->getQuestionsByThemes())) {
                                    $values['questions'] = HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED;
                                }

                                if ($test->adaptive) {
                                    $values['questions'] = HM_Test_TestModel::QUESTIONS_ADAPTIVE;
                                }
                            }

                            break;
                    }

                    $questId = $this->_getParam('quest_id', null);
                    $formStep1Values = $form->getSubForm('step1')->getSession();
                    if (($lesson->getType() == HM_Event_EventModel::TYPE_TEST ||
                            $formStep1Values['step1']['event_id'] == HM_Event_EventModel::TYPE_TEST) && ($questId !== null)) {
                        $values['module'] = $questId;
                    }

                    $form->setDefaults($values);
                    switch ($lesson->getType()) {
                        case HM_Event_EventModel::TYPE_LECTURE:
                            // Инициализация treeSelect
                            if ($form->getSubForm('step2')->getElement('module')) {
                                $params = $lesson->getParams();

                                /** @var HM_Course_Item_ItemService $courseItemService */
                                $courseItemService = $this->getService('CourseItem');
                                list($plainTree) = $courseItemService->getTree($params['course_id'], $params['module_id']);
                                $parentId = $plainTree[0]['item']->oid;
                                if (!empty($plainTree[0]['parent'])) {
                                    $parentId = $plainTree[0]['parent']['item']->oid;
                                }

                                $form->getSubForm('step2')->getElement('module')->jQueryParams['itemId'] = $parentId;
                            }
                            break;
                    }
                }
            }
        }

        $this->view->form = $form;
        $this->view->subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
    }

    /**
     * #7590
     * Проверка дат при создании-обновлении занятия.
     * Если курс с регламентированными датами и правит-создает занятие не автор курса,
     * то не даем выскочить за рамки курса
     * @param $form
     * @param $subjectId
     * @return bool - были или нет внесены изменения в даты занятия (TRUE-были)
     */
    private function checkLessonDates($form, $subjectId) {
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));
        $result = FALSE;
        if ($subject) {
            if ($subject->period == HM_Subject_SubjectModel::PERIOD_DATES /* && $subject->author_id != $this->getService('User')->getCurrentUserId() */) {
                $beginSubject = strtotime($subject->begin_planned);
                $endSubject   = strtotime($subject->end_planned);

                if ($beginSubject || $endSubject) {
                    if ($form->getValue('beginDate') && $form->getValue('endDate')) {
                        $beginLesson  = strtotime($form->getValue('beginDate'));
                        $endLesson    = strtotime($form->getValue('endDate'));

                        if ($subject->begin_planned && ($beginLesson - $beginSubject) < 0 || ($endSubject - $beginLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getSubForm('step1')->getElement('beginDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }

                        if ($subject->end_planned && ($endSubject - $endLesson) < 0 || ($endLesson - $beginSubject) < 0 ) {
                            $date = new HM_Date($endSubject);
                            $form->getSubForm('step1')->getElement('endDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }

                    if ($form->getValue('currentDate')) {
                        $curLesson  = strtotime($form->getValue('currentDate'));
                        if ($beginSubject && ($curLesson - $beginSubject) < 0 || ($endSubject - $curLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getSubForm('step1')->getElement('currentDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }
                }
            }
        }

        return $result;
    }
    public function editIconAction() {
        $form = new HM_Form_Icon();
        $request = $this->getRequest();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if ($form->getValue('icon') != null) {
                    HM_Lesson_LessonService::updateIcon($lessonId, $form->getElement('icon'));
                } else {
                    HM_Lesson_LessonService::updateIcon($lessonId, $form->getElement('server_icon'));
                }
            }
            $params = array();
            $params['subject_id'] = $subjectId;
            $params['switcher'] = 'my';
            $this->_redirector->gotoSimple('index', 'list', 'lesson', $params);
            exit;
        }

        $this->view->form = $form;
    }

    public function editDatesAction()
    {
        $subjectId = (int)$this->_getParam('subject_id', 0);

        $lessonId = (int)$this->_getParam('lesson_id', 0);
        $userId = (int)$this->_getParam('user_id', 0);

        $lessonAssign = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAllDependence(array('Lesson', 'User'), array(
            'SHEID = ?' => $lessonId,
            'MID = ?' => $userId,
        )));

        if ($lessonAssign) {

            $lesson = $lessonAssign->lessons->current();
            $user = $lessonAssign->users->current();
            $title = sprintf('%s / %s', $lesson->title, $user->getName());
            $this->view->setSubHeader($title);

            $form = new HM_Form_LessonDates();

            $request = $this->getRequest();
            $formValues = $request->getPost();
            if ($request->isPost() && $form->isValid($formValues)) {

                switch ($formValues['GroupDate']) {
                    case HM_Lesson_LessonModel::TIMETYPE_FREE:
                        $lessonAssign->begin_personal = $lessonAssign->end_personal = 0;
                        break;

                    case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                        try {
                            $begin = new HM_Date($formValues['currentDate'] . ' ' . $formValues['beginTime']);
                        } catch (Zend_Date_Exception $e) {
                            $begin = new HM_Date();
                        }
                        try {
                            $end = new HM_Date($formValues['currentDate'] . ' ' . $formValues['endTime']);
                        } catch (Zend_Date_Exception $e) {
                            $end = new HM_Date();
                        }
                        $lessonAssign->begin_personal = $begin->toString('YYYY-MM-dd HH:mm');
                        $lessonAssign->end_personal = $end->toString('YYYY-MM-dd HH:mm');

                        break;

                        $lesson = $this->getService('Lesson')->update(
                            array(
                                'SHEID' => $form->getValue('lesson_id'),
                                'title' => $form->getValue('title'),
                                'CID' => $form->getValue('subject_id'),
                                'typeID' => $form->getValue('event_id'),
                                'vedomost' => $form->getValue('vedomost'),
                                'teacher' => $form->getValue('teacher'),
                                'moderator' => $form->getValue('moderator'),
                                'createID' => $this->getService('User')->getCurrentUserId(),
                                'recommend' => $form->getValue('recommend'),
                                'all' => $form->getValue('all'),
                                'GroupDate' => $form->getValue('GroupDate'),
                                'beginDate' => $form->getValue('beginDate'),
                                'endDate' => $form->getValue('endDate'),
                                'currentDate' => $form->getValue('currentDate'),
                                'beginTime' => $form->getValue('beginTime'),
                                'endTime' => $form->getValue('endTime'),
                                'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
                                'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
                                'Condition' => $form->getValue('Condition'),
                                'cond_sheid' => $form->getValue('cond_sheid'),
                                'cond_mark' => ((null !== $form->getValue('cond_mark')) ? $form->getValue('cond_mark') : ''),
                                'cond_progress' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_progress') : 0),
                                'cond_avgbal' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_avgbal') : 0),
                                'cond_sumbal' => ((null !== $form->getValue('cond_sumbal')) ? $form->getValue('cond_sumbal') : 0),
                                'gid' => $form->getValue('subgroups'),
                                'notice' => $form->getValue('notice'),
                                'notice_days' => (int)$form->getValue('notice_days'),
                                'activities' => $activities,
                                'descript' => $form->getValue('descript'),
                                'tool' => $tool,
                                'threshold' => ((null !== $form->getValue('threshold')) ? $form->getValue('threshold') : 0),
                            )
                        );

                        if ($lesson) {

                            $students = $form->getValue('students');
                            if (!is_array($students)) {
                                $students = $students == 0 ? array() : array($students);
                            }

                            $groupId = $form->getValue('subgroups');
                            $group = explode('_', $groupId);

                            /* TODO Отписываем людей которые в ручнов выборе, если выбрана группа подгруппа? Помоему туда постоянно поподает 0 */
                            if ($group[0] == 'sg' || $group[0] == 's') {
                                $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
                                $this->getService('StudyGroupCourse')->removeLessonFromGroups($subjectId, $lesson->SHEID);
                            }

                            /* Параметр Учебная группа */
                            if ($group[0] == 'sg') {
                                $groupId = (int)$group[1];
                                $students = $this->getService('StudyGroup')->getUsers($groupId);
                                /* Добавляем запись что группа подписана на урок */
                                $this->getService('StudyGroupCourse')->addLessonOnGroup($subjectId, $lesson->SHEID, $groupId);
                            }

                            /* Параметр Подгруппа */
                            if ($group[0] == 's') {
                                $groupId = (int)$group[1];
                                if ($groupId > 0) {
                                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                                    $res = array();
                                    foreach ($students as $value) {
                                        $res[] = $value->mid;
                                    }
                                    $students = $res;
                                }
                            }


                            if (!$form->getValue('switch')) {
                                $students = $lesson->getService()->getAvailableStudents($subjectId);
                            }

                            /**
                             * тут что-то связано с вебинарами,
                             * принудительная запись модераторов и учителей в студенты - нарушает работу системы
                             * TODO: разобраться для чего это сделано и пофиксить по человечески
                             *
                             * if (is_array($students)) {
                             * if ($form->getValue('moderator') !== null)
                             * $students[] = $form->getValue('moderator');
                             * $students[] = $form->getValue('teacher');
                             * $students = array_unique($students);
                             * }
                             * else {
                             * $students = array($form->getValue('moderator'), $form->getValue('teacher'));
                             * }
                             */

                            $userVariants = array_filter($form->getValue('user_variant', array())); // filter_empty

                            if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                                $students = array_keys($userVariants);
                            }

                            if (!is_array($students)) {
                                $students = $students == 0 ? array() : array($students);
                            }

                            $this->_postProcess($lesson, $form, $students);
                            $lesson->setParams($this->_prepareParams($form, $lesson));

                            $this->getService('Lesson')->update($lesson->getValues());

                            //$this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                            if ((($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                                || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL))) {
                                $this->assignStudents($lesson->SHEID, $students, $userVariants);
                            }
                        }

                        if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                            $this->view->cancelUrl = $form->getValue('cancelUrl');
                            return true;
                        } else {
                            $this->_flashMessenger->addMessage(_('Занятие успешно изменено'));
                            if ($checkResult) {
                                $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                            }

                            if ($form->getValue('fromList')) {
                                $url = array(
                                    'module' => 'lesson',
                                    'controller' => 'list',
                                    'action' => 'my',
                                    'subject_id' => $lesson->CID,
                                    'user_id' => $user->MID,
                                );
                                $this->_redirector->gotoUrl($this->view->url($url, null, true) . '#lesson_' . $lessonId);

                            } else {

                                $values = array(
                                    'beginDate' => HM_Model_Abstract::date($lessonAssign->begin_personal),
                                    'endDate' => HM_Model_Abstract::date($lessonAssign->end_personal),
                                    'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_DATES,
                                );

                                if ((!strtotime($values['beginDate']) && !strtotime($values['endDate'])) || ((strtotime($values['beginDate']) < 0) && (strtotime($values['endDate']) < 0))) {

                                    $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                                    unset($values['beginDate']);
                                    unset($values['endDate']);

                                } elseif ($values['beginDate'] == $values['endDate']) {

                                    $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_TIMES;
                                    $values['currentDate'] = $values['beginDate'];
                                    $values['beginTime'] = HM_Model_Abstract::timeWithoutSeconds($lessonAssign->begin_personal);
                                    $values['endTime'] = HM_Model_Abstract::timeWithoutSeconds($lessonAssign->end_personal);
                                    unset($values['beginDate']);
                                    unset($values['endDate']);
                                }

                                $form->setDefaults($values);
                            }
                        }


                        $this->view->form = $form;
                        $this->view->subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
                }
            }
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Занятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
        }
    }

    public function redirectDialogAction()
    {
        $this->view->redirectUrl = $this->_getParam('redirectUrl');
    }

    public function deleteAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $switcher = $this->_getParam('switcher', 0);

        if ($lessonId) {
            /** @var HM_Eclass_EclassService $eclassService */
            $eclassService = $this->getService('Eclass');
            $eclassService->webinarDelete($this->getService('Lesson')->find($lessonId)->current()->webinar_event_id);

            $this->getService('Lesson')->delete($lessonId);
        }

        $this->_flashMessenger->addMessage(_('Занятие успешно удалено'));

        $params = array(
            'subject_id' => $subjectId
        );

        if ($switcher) {
            $params['switcher'] = $switcher;
        }

        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, $params);
    }

    public function deleteByAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $lessonIds = $this->_request->getParam('postMassIds_grid');
        $lessonIds = explode(',', $lessonIds);

        $eclassService = $this->getService('Eclass');
        if (is_array($lessonIds) && count($lessonIds)) {
            foreach ($lessonIds as $id) {
                $eclassService->webinarDelete($this->getService('Lesson')->find($id)->current()->webinar_event_id);
                $this->getService('Lesson')->delete($id);
            }
        }

        $this->_flashMessenger->addMessage(_('Занятия успешно удалены'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
    }

    public function updateName($field, $id, $type)
    {

        if ($type == HM_Event_EventModel::TYPE_COURSE) {

            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($id));

            $courseId = $lesson->getModuleId();

            $course = $this->getService('Course')->getOne($this->getService('Course')->find($courseId));

            if ($course->new_window == 1) {
                $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('subject_id', 0), $courseId);
                if ($itemId != false) {
                    return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)) . '" target = "_blank">' . $field . '</a>';
                }
            }
        }
        if ($type == HM_Event_EventModel::TYPE_TASK
            && $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), array(Roles::ROLE_TEACHER, Roles::ROLE_DEAN)
            )
        ) {
            $url = $this->view->url(
                array(
                    'module' => 'lesson',
                    'controller' => 'result',
                    'action' => 'extended',
                    'lesson_id' => $id,
                    'subject_id' => $this->_getParam('subject_id')
                )
            );
            return '<a href="' . $url . '" title="' . _('Просмотр занятия') . '">' . $field . '</a>';
        }

        $target = ($type == HM_Event_EventModel::TYPE_ECLASS) ? ' target="_blank" ' : '';
        return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $id, 'subject_id' => $this->_getParam('subject_id'))) . '" title="' . _('Просмотр занятия') . '"' . $target . '>' . $field . '</a>';
    }

    private function _postProcess(HM_Lesson_LessonModel $lesson, Zend_Form $form, $students) {
        switch ($lesson->getType()) {
            case HM_Event_EventModel::TYPE_TASK:
                /*$abstract = $this->getOne($this->getService('Task')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $lesson, $form);*/
                break;
            case HM_Event_EventModel::TYPE_ECLASS:
                $this->_postProcessEclass($lesson, $form, $students);
                break;
            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                /** @var HM_Quest_QuestModel $quest */
                $quest = $this->getOne($this->getService('Quest')->find($form->getValue('module')));
                $this->_postProcessQuest($quest, $lesson, $form);
                break;
            default:
                $this->getService('Test')->deleteBy($this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID));

                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$lesson->typeID])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($lesson->typeID);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onLessonUpdate($lesson, $form);
                        }
                    }
                }
        }
    }

    private function _postProcessEclass($lesson, $form, $students) {
        $eclassService = $this->getService('Eclass');

        $eclassService->webinarPush(array(
                'lesson'   => $lesson,
                'students' => $students,
            )
        );
    }
    /**
     * @param HM_Quest_QuestModel $quest
     * @param HM_Lesson_LessonModel $lesson
     * @param Zend_Form $form
     */
    private function _postProcessQuest($quest, $lesson, $form) {
        // Будем копировать настройки из области видимости курса
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT, $lesson->CID);

        /** @var HM_Quest_Settings_SettingsService $questSettingsService */
        $questSettingsService = $this->getService('QuestSettings');

        // Устанавливаем свою область видимости для занятий
        /** @var HM_Quest_Settings_SettingsModel $settings */
        $settings = $questSettingsService->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $lesson->SHEID);

        /**
         * Индивидульная настройка теста для занятия.
         * Раскомментировать при необходимости.
         */
        list($dataQuest, $dataSettings) = HM_Quest_Settings_SettingsModel::split($form->getValues());

        $dataSettings['quest_id']   = $settings->quest_id;
        $dataSettings['scope_type'] = $settings->scope_type;
        $dataSettings['scope_id']   = $settings->scope_id;


        if ($dataSettings['mode_selection'] == HM_Quest_QuestModel::MODE_SELECTION_LIMIT_BY_CLUSTER) {
            $dataSettings['mode_selection_questions'] = $dataSettings['mode_selection_questions_cluster'];
        }
        unset($dataSettings['mode_selection_questions_cluster']);

        if(!$dataSettings['mode_display']) {
            $dataSettings['mode_display'] = $settings->mode_display;
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_CLUSTERS) {
            $dataSettings['mode_display_clusters'] = new Zend_Db_Expr('NULL');
        }

        if ($dataSettings['mode_display'] != HM_Quest_QuestModel::MODE_DISPLAY_LIMIT_QUESTIONS) {
            $dataSettings['mode_display_questions'] = new Zend_Db_Expr('NULL');
        } elseif($settings->mode_display_questions && !$dataSettings['mode_display_questions']) {
            $dataSettings['mode_display_questions'] = $settings->mode_display_questions;
        }


        if (!$dataSettings['cluster_limits']) {
            $dataSettings['cluster_limits'] = $settings->cluster_limits;
        }
        if (!$dataSettings['limit_time']) {
            $dataSettings['limit_time'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_attempts']) {
            $dataSettings['limit_attempts'] = new Zend_Db_Expr('NULL');
        }
        if (!$dataSettings['limit_clean']) {
            $dataSettings['limit_clean'] = new Zend_Db_Expr('NULL');
        }
        $questSettingsService->update($dataSettings);

        /*
        $elements = $form->getSubForm('step2')->getDisplayGroup('quest_settings')->getElements();
        foreach ($elements as $element) {
            $elementName = $element->getName();
            $elementValue = $element->getValue();
            if (isset($elementValue) && isset($settings->$elementName)) {
                $settings->$elementName = $elementValue;
            }
        }

        $questSettingsService->update($settings->getData());
        */
    }

    /**
     * Проверяет если Задание ещё не создано, то создаёт
     * @param $lesson
     * @param Zend_Form $form
     */
    private function _preProcessTest(HM_Lesson_LessonModel $lesson, Zend_Form $form) {

        if($lesson->getType() == HM_Event_EventModel::TYPE_TASK) {
            /** @var HM_Task_TaskService $abstractTest */
            $taskService = $this->getService('Task');
            /** @var HM_Test_TestService $testService */
            $testService = $this->getService('Test');

            /** @var HM_Task_TaskModel $abstractTest */
            $abstractTest = $this->getOne($taskService->find($form->getValue('module')));

            if ($abstractTest) {
                $test = $this->getOne($testService->fetchAll(
                    $testService->quoteInto(
                        array('lesson_id = ?', ' AND test_id = ?', ' and datatype = ?'),
                        array($lesson->SHEID, $form->getValue('module'), HM_Test_TestModel::TYPE_TASK)
                    )
                ));

                if ( ! $test) {
                    $testService->deleteBy($this->getService('Test')
                        ->quoteInto(
                            array('lesson_id = ?', ' and datatype = ?'),
                            array($lesson->SHEID, HM_Test_TestModel::TYPE_TASK)
                        )
                    );

                    $testService->insert(
                        array(
                            'cid' => $lesson->CID,
                            'datatype' => HM_Test_TestModel::TYPE_TASK,
                            'sort' => 0,
                            'free' => 0,
                            'rating' => 0,
                            'status' => 1,
                            'last' => 0,
                            'cidowner' => $lesson->CID,
                            'title' => $lesson->title,
                            'data' => $abstractTest->data,
                            'lesson_id' => $lesson->SHEID,
                            'test_id' => $form->getValue('module'),
                            'mode' => $form->getValue('mode'),
                            'lim' => $form->getValue('lim'),
                            'qty' => $form->getValue('qty'),
                            'startlimit' => $form->getValue('startlimit'),
                            'limitclean' => $form->getValue('limitclean'),
                            'timelimit' => $form->getValue('timelimit'),
                            'random' => $form->getValue('random'),
                            'adaptive' => (int) ($form
                                    ->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE),
                            'questres' => $form
                                ->getValue('questres') !== null ? $form->getValue('questres') : 0,
                            'showurl' => $form
                                ->getValue('showurl') !== null ? $form->getValue('showurl') : 0,
                            'endres' => $form
                                ->getValue('endres') !== null ? $form->getValue('endres') : 0,
                            'skip' => $form->getValue('skip'),
                            'allow_view_log' => $form->getValue('allow_view_log'),
                            'comments' => $form->getValue('comments'),
                            'type' => $abstractTest->getTestType(),
                            'threshold' => $form->getValue('threshold'),
                        )
                    );
                }
            }
        }
    }

    /**
     * @param HM_Form $form
     * @param HM_Lesson_LessonModel $lesson
     * @return mixed
     */
    protected function _prepareParams($form, $lesson)
    {
        $moduleId = $form->getValue('module');
        $params = $lesson->getParams();

        if ($form->getValue('module')) {
            $params['module_id'] = $form->getValue('module');
        }

        if ($form->getValue('assign_type')) {
            $params['assign_type'] = $form->getValue('assign_type');
        } elseif (isset($params['assign_type']) && $params['assign_type']) {
            unset($params['assign_type']);
        }

        if ($form->getValue('is_hidden', 0)) {
            $params['is_hidden'] = $form->getValue('is_hidden');
        } elseif (isset($params['is_hidden']) && $params['is_hidden']) {
            unset($params['is_hidden']);
        }

        if ($form->getValue('formula')) {
            $params['formula_id'] = $form->getValue('formula');
        } elseif (isset($params['formula_id'])) {
            unset($params['formula_id']);
        }

        if ($form->getValue('formula_group')) {
            $params['formula_group_id'] = $form->getValue('formula_group');
        }

        if ($form->getValue('formula_penalty')) {
            $params['formula_penalty_id'] = $form->getValue('formula_penalty');
        }

        if ($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) {
            $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
        }

        return $params;
    }

    private function _postProcessTest($abstractTest, HM_Lesson_LessonModel $lesson, Zend_Form $form) {
        if ($abstractTest) {

            $test = $this->getOne($this->getService('Test')->fetchAll(
                            $this->getService('Test')->quoteInto(
                                    array('lesson_id = ?', ' AND test_id = ?', ' and datatype = ?'),
                                    array($lesson->SHEID, $form->getValue('module'), HM_Test_TestModel::TYPE_TASK)
                            )
                    ));

            if ($test) {
                // assign values
                $test->test_id = $form->getValue('module');
                $test->title = $lesson->title;
                $test->data = $abstractTest->data;
                $test->mode = $form->getValue('mode');
                $test->lim = $form->getValue('lim');
                $test->qty = $form->getValue('qty');
                $test->startlimit = $form->getValue('startlimit');
                $test->limitclean = $form->getValue('limitclean');
                $test->timelimit = $form->getValue('timelimit');
                $test->random = $form->getValue('random');
                $test->adaptive = (int) ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE);
                $test->questres = $form->getValue('questres');
                $test->showurl = $form->getValue('showurl');
                $test->endres = $form->getValue('endres');
                $test->skip = $form->getValue('skip');
                $test->allow_view_log = $form->getValue('allow_view_log');
                $test->comments = '';
                $test->type = $abstractTest->getTestType();
                $test->threshold = $form->getValue('threshold');

                $test = $this->getService('Test')->update($test->getValues());
            } else {
                $this->getService('Test')->deleteBy(
                        $this->getService('Test')->quoteInto(
                                array('lesson_id = ?', ' and datatype = ?'),
                                array($lesson->SHEID, HM_Test_TestModel::TYPE_TASK)
                        )
                );
                $test = $this->getService('Test')->insert(
                        array(
                            'cid' => $lesson->CID,
                            'datatype' => HM_Test_TestModel::TYPE_TASK,
                            'sort' => 0,
                            'free' => 0,
                            'rating' => 0,
                            'status' => 1,
                            'last' => 0,
                            'cidowner' => $lesson->CID,
                            'title' => $lesson->title,
                            'data' => $abstractTest->data,
                            'lesson_id' => $lesson->SHEID,
                            'test_id' => $form->getValue('module'),
                            'mode' => $form->getValue('mode'),
                            'lim' => $form->getValue('lim'),
                            'qty' => $form->getValue('qty'),
                            'startlimit' => $form->getValue('startlimit'),
                            'limitclean' => $form->getValue('limitclean'),
                            'timelimit' => $form->getValue('timelimit'),
                            'random' => $form->getValue('random'),
                            'adaptive' => (int) ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE),
                            'questres' => $form->getValue('questres') !== null ? $form->getValue('questres') : 0,
                            'showurl' => $form->getValue('showurl') !== null ? $form->getValue('showurl') : 0,
                            'endres' => $form->getValue('endres') !== null ? $form->getValue('endres') : 0,
                            'skip' => $form->getValue('skip'),
                            'allow_view_log' => $form->getValue('allow_view_log'),
                            'comments' => $form->getValue('comments'),
                            'type' => $abstractTest->getTestType(),
                            'threshold' => $form->getValue('threshold'),
                        )
                );
            }


            // unmanaged тесты ушли
            // будем избавляться от зависимостей в коде
            //пока вернем как было, потому что через тесты реализован механизм сохранения
            //заданий (рафакторить весь HM_Question_QuestionService!)
            if ($test) {

//                $this->getService('TestTheme')->deleteBy(
//                        $this->getService('TestTheme')->quoteInto(
//                                array('tid = ?', ' AND cid = ?'), array($test->tid, $test->cid)
//                        )
//                );
//
//                if ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED) {
//                    $this->getService('TestTheme')->insert(
//                            array(
//                                'tid' => $test->tid,
//                                'cid' => $test->cid,
//                                'questions' => $form->getValue('questions_by_theme')
//                            )
//                    );
//                }

                $form->setDefault('module', $test->tid);
            }
        }

    }

    public function themesAction() {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $testId = (int) $this->_getParam('test_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $themes = array(''=>0);//_('Без темы') => 0);
        // Делаем выборку всех тем всех вопросоа теста, в т.ч. и "пустую"
        if ($testId) {
            $test = $this->getOne($this->getService('Quest')->find($testId));
            if ($test) {
                $questions = $test->getQuestionsIds();
                if (count($questions)) {
                    $collection = $this->getService('Question')->fetchAll(
                            $this->getService('Question')->quoteInto('kod IN (?)', $questions), 'qtema'
                    );
                    if (count($collection)) {
                        foreach ($collection as $question) {
                            if (!isset($themes[$question->qtema])) {
                                $themes[$question->qtema] = 0;
                            }
                        }
                    }
                }
            }
        }

        if (count($themes) == 1) {
            $themes = array();
        } else {
            if ($lessonId) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                                $this->getService('Test')->quoteInto('lesson_id = ?', $lessonId)
                        ));
                if ($test) {
                    $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
                                    $this->getService('TestTheme')->quoteInto(
                                            array('tid = ?', ' AND cid = ?'), array($test->tid, $subjectId)
                                    )
                            ));
                    if ($theme) {
                        $questionsByThemes = $theme->getQuestionsByThemes();
                        if (is_array($questionsByThemes) && count($questionsByThemes)) {
                            foreach ($questionsByThemes as $theme => $count) {
                                $theme = $theme?$theme:''; // В базе "без темы" - это 0
                                if (isset($themes[$theme])) {
                                    $themes[$theme] = $count;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->view->themes = $themes;
    }

    public function orderSectionAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $sectionId = $this->_getParam('section_id', array());
        $materials = $this->_getParam('material', array());
        echo $this->getService('Section')->setLessonsOrder($sectionId, $materials) ? 1 : 0;
    }

    public function exportVariantsAction()
    {
        //$this->getService('Quest')->exportVariants(array(560), 2);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $variantCount = $this->_getParam('variant_count', 1);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                try {
                    $pdfContent = $this->getService('Quest')->exportVariants($ids, $variantCount);
                } catch (Exception $exc) {
                    $this->_flashMessenger->addMessage(array('message' => _($exc->getMessage()), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
                }

                if ($pdfContent) {
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="test_variants.pdf"');
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false);
                    header("Pragma: public");
                    header("Content-Transfer-Encoding: binary");
                    die($pdfContent);
                }
            }
        }
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));

    }
}
