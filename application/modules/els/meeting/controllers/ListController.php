<?php
class Meeting_ListController extends HM_Controller_Action_Project
{

    protected $_formName = 'HM_Form_Meeting';
    protected $_module = 'meeting';
    protected $_controller = 'list';

    public function init()
    {
        parent::init();
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {
            $this->_helper->ContextSwitch()
                ->setAutoJsonSerialization(true)
                ->addActionContext('calendar', 'json')
                ->addActionContext('save-calendar', 'json')
                ->initContext('json');
            unset($this->view->withoutContextMenu);
        }
    }


    public function timelineAction()
    {
        $userId = (int)$this->_getParam('user_id', 0);

        $view = $this->view;
        $base = Zend_Registry::get('config')->url->base;

        $data    = $this->getService('Meeting')->fetchAll(
            $this->getService('Meeting')->quoteInto(array('project_id = ? ','AND isfree = ?'), array($this->_getParam('project_id', 0),HM_Meeting_MeetingModel::MODE_PLAN)));


        foreach ($data as  $meeting) {

            $url = $meeting->getExecuteUrl().'/meeting/1';//Залипуха

            $timelineData = array();
            $timelineData['name'] = $url ? "<a href='{$url}'>{$meeting->title}</a>" : $meeting->title;
            $timelineData['image'] = "/images/content-modules/timeline/{$meeting->typeID}.png";
            $timelineData['date_'] = $meeting->begin;

            $type = $this->getService('Classifier')->getItemClassifiers($meeting->meeting_id, HM_Classifier_Link_LinkModel::TYPE_MEETING)->current();
            $type = isset($type) ? $type->classifier_id : 0;

            $timelineData['type'] = $type;
//            $timelineData['sub_type'] = 4;
            $timelineData['text'] = $meeting->descript;

            $users = $this->getService('MeetingAssign')->fetchAll(array('meeting_id=?' => $meeting->meeting_id));
            $currentUser = false;
            foreach($users as $_user) {
                if($_user->MID==$this->getService('User')->getCurrentUserId()) 
                {
                    $currentUser = $_user;
                    break;
                }
            }    
//pr($currentUser);

            
            $ids = $users->getList('MID');
            $ids[] = -1;
            $users = $this->getService('User')->fetchAll(array('MID IN ('.implode(',', $ids).')'));
            if(count($users)) {
                $users2 = array();
                foreach($users  as $user) {
                    $users2[] = $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => $user->MID))).'&nbsp;'.$user->getName();
                }
                $timelineData['text'] .= "<BR>Участники: <br>".implode('<br>', $users2)."";
            }

            $params = explode(';', $meeting->params);
            $params = explode('=', $params[0]);
            $executableID = (isset($params[0]) && $params[0]=='module_id' && isset($params[1]) && $params[1]) ? $params[1] : false;

            if($meeting->typeID==HM_Event_EventModel::TYPE_RESOURCE) {
                if($executableID!==false) {
                    $resource = $this->getOne($this->getService('Resource')->find($params[1]));
//                    if($resource->type==2 && $resource->url) 
                    {
                        $timelineData['text'] .= "<BR><a target=top href='{$resource->url}'>{$resource->url}</a>";  
                    }
                }
            }

            if($currentUser) {
                $timelineData['text'] .= "<BR>Выполнено: ";  
                $timelineData['text'] .= ($currentUser->V_STATUS==-1 ? 'нет' : ($currentUser->V_STATUS==1 ? "УСПЕШНО" : "{$currentUser->V_STATUS}%"));
                $timelineData['comments'] = $currentUser->comments;
            }
            if($meeting->typeID==HM_Event_EventModel::TYPE_POLL && $executableID!==false) {
                $timelineData['comments'] .= "<BR>Результаты: <a href='/quest/report/diagram/quest_id/{$executableID}/switcher/diagram'>смотреть</a>";  
            }


            $timeline[] = $timelineData;
        }

        function cmp($a, $b)
        {
            if ($a['date_'] == $b['date_']) {
                return 0;
            }
            return ($a['date_'] < $b['date_']) ? -1 : 1;
        }
        usort($timeline, 'cmp');

        $view->assign(array(
            'data' => $timeline
        ));

        $view->headLink()->appendStylesheet(
            $base.'js/lib/vertical-timeline/css/style.css'
        );
        $view->headScript()->appendFile(
            $base.'js/lib/vertical-timeline/js/main.js'
        );



    }


    public function calendarAction()
    {
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {
        $begin = $this->getService('Project')->getDateTime(intval($this->_getParam('start')));
        $end   = $this->getService('Project')->getDateTime(intval($this->_getParam('end')));
        //$where = $this->getService('Project')->quoteInto(array('base=?',' AND  NOT ( begin >= ?',' AND end <= ?)'),array(HM_Project_ProjectModel::BASETYPE_SESSION, $end, $begin));

        $collection    = $this->getService('Meeting')->fetchAll(
            $this->getService('Meeting')->quoteInto(array('project_id = ? ','AND isfree = ?'), array($this->_getParam('project_id', 0),HM_Meeting_MeetingModel::MODE_PLAN)));

        $eventsSources = $this->getService('Meeting')->getCalendarSource($collection, '0000ff', true);
        $this->view->assign($eventsSources);
        }
        else{
            $this->view->source = array('module'=>'meeting', 'controller'=>'list', 'action'=>'calendar');
            $this->view->editable = false;//($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT) ? false: true;
        }
    }

    public function generateAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $i = 0;
        //$participants = $meeting->getService()->getAvailableParticipants($projectId);

        // Тесты более не могут быть свободными
        /**
         * Да, но нужно чтобы при генерации тесты которые относятся к уроку добавлялись в занятия.
         * Поэтому раскомментировано обратно.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 14.01.2013
         */
        $tests = $this->getService('TestAbstract')->fetchAllDependenceJoinInner('ProjectAssign', 'ProjectAssign.project_id = '. $projectId);
        foreach($tests as $value){

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->test_id));
            $testIds = $tests->getList('tid', 'title');

            $meetings = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	//'params LIKE "%module_id=' . $test->tid . ';%" ',
                    'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );
            foreach($meetings as $meeting){
                if(in_array($meeting->getModuleId(), array_keys($testIds))){
                    continue 2;
                }
            }

            $form = new $this->_formName();
            $form->getSubForm('step2')->initTest();
            $form->populate(
                array(
                    'title' => $value->title,
                    'event_id' => HM_Event_EventModel::TYPE_TEST,
                    'vedomost' => 1,

                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->test_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array(),
                    'isfree' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }

        // Электронные курсы
        $courses = $this->getService('Course')->fetchAllDependenceJoinInner('ProjectAssign', 'ProjectAssign.project_id = '. $projectId);
        foreach($courses as $value){

            $meeting = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	'params LIKE ?' => '%module_id=' . $value->project_id . ';%',
                    'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );

            if(count($meeting) > 0){
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Meeting')->updateWhere(
                array(
                    'isfree' => HM_Meeting_MeetingModel::MODE_FREE_BLOCKED,
                ),
                array(
                    'project_id = ?' => $projectId,
                    'params LIKE ?' => '%module_id=' . $value->project_id . ';%',
                    'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_FREE,
                )
            );

            $form = new $this->_formName();
            $form->getSubForm('step2')->initCourse();
            $form->populate(
                array(
                    'title' => $value->Title,
                    'event_id' => HM_Event_EventModel::TYPE_COURSE,
                    'vedomost' => 1,
                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->project_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array(),
                    'isfree' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }


        // Ресурсы
        $resources = $this->getService('Resource')->fetchAllDependenceJoinInner('ProjectAssign', 'ProjectAssign.project_id = '. $projectId);
        foreach($resources as $value){

            $meeting = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	"params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                    'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );

            if(count($meeting) > 0){
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Meeting')->updateWhere(
                array(
                    'isfree' => HM_Meeting_MeetingModel::MODE_FREE_BLOCKED,
                ),
                array(
                    'project_id = ?' => $projectId,
                    "params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                    'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_FREE,
                )
            );

            $form = new $this->_formName();
            $form->getSubForm('step2')->initResource();
            $form->populate(
                array(
                    'title' => $value->title,
                    'event_id' => HM_Event_EventModel::TYPE_RESOURCE,
                    'vedomost' => 1,
                    'moderator' => 0,
                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_meeting_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->resource_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array(),
                    'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN,
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }



        //Опросы
        $polls = $this->getService('Poll')->fetchAllDependenceJoinInner('ProjectAssign', 'ProjectAssign.project_id = '. $projectId);
        foreach($polls as $value){

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->quiz_id));
            $testIds = $tests->getList('tid', 'title');

            $meetings = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	//'params LIKE "%module_id=' . $test->tid . ';%" ',
                    'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                )
            );
            foreach($meetings as $meeting){
                if(in_array($meeting->getModuleId(), array_keys($testIds))){
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
                    'moderator' => 0,
                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->quiz_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array()
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }


            //webinars
        $webinars = $this->getService('Webinar')->fetchAll(array('subjects_id = ?' => $projectId));
        foreach($webinars as $value){

            $meeting = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	'params LIKE ?' => '%module_id=' . $value->webinar_id . ';%',
                    'typeID = ?' => HM_Event_EventModel::TYPE_WEBINAR
                )
            );
            if(count($meeting) > 0){
                continue;
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initPoll();
            $form->populate(
                array(
                    'title' => $value->name,
                    'event_id' => HM_Event_EventModel::TYPE_WEBINAR,
                    'vedomost' => 1,
                    'moderator' => 0,
                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->webinar_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array()
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }


        //Tasks
        $tasks = $this->getService('Task')->fetchAllDependenceJoinInner('ProjectAssign', 'ProjectAssign.project_id = '. $projectId);
        foreach($tasks as $value){

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->task_id));
            $testIds = $tests->getList('tid', 'title');

            $meetings = $this->getService('Meeting')->fetchAll(
                array(
                	'project_id = ?' => $projectId,
                	//'params LIKE "%module_id=' . $test->tid . ';%" ',
                    'typeID = ?' => HM_Event_EventModel::TYPE_TASK
                )
            );
            foreach($meetings as $meeting){
                if(in_array($meeting->getModuleId(), array_keys($testIds))){
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
                    'moderator' => 0,
                    'moderator' => 0,
                    'recommend' => 1,
                    'all' => 1,
                    'GroupDate' => HM_Meeting_MeetingModel::TIMETYPE_FREE,
                    'beginDate' => '',
                    'endDate' => '',
                    'currentDate' => '',
                    'beginTime' => '',
                    'endTime' => '',
                    'beginRelative' => '',
                    'endRelative' => '',
                    'Condition' => HM_Meeting_MeetingModel::CONDITION_NONE,
                    'cond_project_id' => '',
                    'cond_mark' => '',
                    'cond_progress' => '',
                    'cond_avgbal' => '',
                    'cond_sumbal' => '',
                	'module' => $value->task_id,
                    'gid' => 0,
                    'formula' => 0,
                    'formula_group' => 0,
                    'formula_penalty' => 0,
                    'participants' => array()
                )
            );
            $this->addMeeting($form);
            unset($form);

            $i++;
        }

         $this->_flashMessenger->addMessage(sprintf(_('Сгенерировано занятий: %s'), $i));
         $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));

    }



    protected function addMeeting($form)
    {
        $projectId = (int) $this->_getParam('project_id', 0);

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
        $moduleId = $form->getValue('module');
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }
        $this->getService('Meeting')->setMeetingFreeMode($moduleId, $typeId, $projectId, HM_Meeting_MeetingModel::MODE_FREE_BLOCKED);

        $data = array(
        		'title'         => $form->getValue('title'),
                'project_id'           => $projectId,
                'typeID'        => $form->getValue('event_id'),
                'vedomost'      => $form->getValue('vedomost'),
                'moderator'       => $form->getValue('moderator'),
                'moderator' => $form->getValue('moderator'),
                'createID'      => $this->getService('User')->getCurrentUserId(),
                'recommend'     => $form->getValue('recommend'),
                'all'           => (int) $form->getValue('all'),
                'GroupDate'     => $form->getValue('GroupDate'),
                'beginDate'     => $form->getValue('beginDate'),
                'endDate'       => $form->getValue('endDate'),
                'currentDate'   => $form->getValue('currentDate'),
                'beginTime'     => $form->getValue('beginTime'),
                'endTime'       => $form->getValue('endTime'),
                'beginRelative' => ($form->getValue('beginRelative'))? $form->getValue('beginRelative') : 1,
                'endRelative' => ($form->getValue('endRelative'))? $form->getValue('endRelative') : 1,
                'Condition'     => $form->getValue('Condition'),
                'cond_project_id'    => (string) $form->getValue('cond_project_id'),
                'cond_mark'     => (string) $form->getValue('cond_mark'),
                'cond_progress' => (string) $form->getValue('cond_progress'),
                'cond_avgbal'   => (string) $form->getValue('cond_avgbal'),
                'cond_sumbal'   => (string) $form->getValue('cond_sumbal'),
                'gid'           => $form->getValue('subgroups'),
                'notice'        => $form->getValue('notice'),
                'notice_days'   => (int) $form->getValue('notice_days'),
                'activities'    => $activities,
                'descript'      => $form->getValue('descript'),
                'tool'          => $tool,
                'isfree'        => HM_Meeting_MeetingModel::MODE_PLAN,
        );

        $meetings = $this->getService('Meeting')->fetchAll(array('project_id = ?' => $projectId));
        $meetingsOrders = $meetings->getList('order');
        if ($meetingsOrders){
            $highestValue = max(array_values($meetingsOrders));
            $highestValue++;
            $data['order'] = $highestValue;
        }

        $meeting = $this->getService('Meeting')->insert($data);

            if ($meeting) {

            $params = $meeting->getParams();

                if($form->getValue('module')) {
                    $params['module_id'] = $form->getValue('module');
                }

                if($form->getValue('assign_type')) {
                    $params['assign_type'] = $form->getValue('assign_type');
                } elseif (isset($params['assign_type']) && $params['assign_type']) {
                    unset($params['assign_type']);
                }

                if($form->getValue('is_hidden',0)) {
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

            if ($form->getValue('event_id') == HM_Event_EventModel::TYPE_LECTURE) {
                $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
            }

                $meeting->setParams($params);
                $this->getService('Meeting')->update($meeting->getValues());

                $participants = $form->getValue('participants');
                $groupId = (int) $form->getValue('subgroups');

                if($groupId > 0){
                    $this->getService('Meeting')->unassignParticipant($meeting->meeting_id, $participants);

                    $participants = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                    $res = array();
                    foreach($participants as $value){
                        $res[] = $value->mid;
                    }
                    $participants = $res;
                }

                if (!$form->getValue('switch')) {
                    $participants = $meeting->getService()->getAvailableParticipants($projectId);
                }

                if (is_array($participants)) {
                    $participants[] = $form->getValue('moderator');
                    $participants[] = $form->getValue('moderator');
                    $participants = array_unique($participants);
                }
                else {
                    $participants = array($form->getValue('moderator'), $form->getValue('moderator'));
                }

                $userVariants = array_filter($form->getValue('user_variant', array())); // filter_empty

                // Это круто кто-то закомментировал условие.....
                if ($form->getValue('assign_type', HM_Meeting_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Meeting_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                    $participants = array_keys($userVariants);
                }

                if (is_array($participants) && count($participants)
                    && (($this->_project->period_restriction_type != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL)
                        || ($this->_project->state == HM_Project_ProjectModel::STATE_ACTUAL )))
                {
                    $this->assignParticipants($meeting->meeting_id, $participants, $userVariants);
                }

				$this->getService('Project')->update(array(
                    'last_updated' => $this->getService('Project')->getDateTime(),
                    'projid' => $projectId
                ));

                $this->_postProcess($meeting, $form, $participants);
            }
    }

    protected function assignParticipants($meetingId, $participants, $taskUserVariants = null){

        if (is_array($participants) && count($participants)) {
            $res = $this->getService('Meeting')->assignParticipants($meetingId, $participants, true, $taskUserVariants);
        } else {
            $this->getService('MeetingAssign')->deleteBy($this->getService('MeetingAssign')->quoteInto('meeting_id = ? AND MID > 0', $meetingId));
        }
    }

    public function indexAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);

        $this->view->headLink()->appendStylesheet($this->view->serverUrl()."/css/content-modules/schedule_table.css");

        $switcher = $this->_getParam('switcher', 0);
        if($switcher && $switcher != 'index'){
        	$this->getHelper('viewRenderer')->setNoRender();
        	$action = $switcher.'Action';
			$this->$action();
			$this->view->render('list/'.$switcher.'.tpl');
			return true;
        }

        $select = $this->getService('Meeting')->getSelect();
        $select->from(array('l' => 'meetings'),
                    array(
                        'meeting_id' => 'l.meeting_id',
                        'TypeID2' => 'l.typeID',
                        'l.title',
                        'l.typeID',
                        'classifiers' => new Zend_Db_Expr('(SELECT name FROM classifiers where classifier_id=MIN(cla.classifier_id))'),
                        'l.begin',
                        'l.end',
                        'l.timetype',
                        //'l.condition',
                        'l.cond_project_id',
                        'l.cond_mark',
                        'l.cond_progress',
                        'l.cond_avgbal',
                        'l.cond_sumbal',
                        'l.isfree',
                        'sort_order' => 'l.order'
                    )
                );
        $select->where('project_id = ?', $projectId)
               ->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
               ->where('l.isfree = ?', HM_Meeting_MeetingModel::MODE_PLAN)
                ->joinLeft(
                    array('cl' => 'classifiers_links'),
                    'l.meeting_id = cl.item_id AND cl.type = 16', // классификатор уч.курсов
                    array()
                )
                ->joinLeft(
                    array('cla' => 'classifiers'),
                    'cl.classifier_id = cla.classifier_id', // классификатор уч.курсов
                    array()
                )
               ->order(array('sort_order'))
                ->group(array('meeting_id', 'typeID', 'title', 'begin', 'end', 'timetype', 'cond_project_id', 'cond_mark', 'cond_progress', 'cond_avgbal', 'cond_sumbal', 'isfree', 'order'));

        if ($this->getService('User')->getCurrentUserRole() != HM_Role_Abstract_RoleModel::ROLE_ADMIN) {
			// нужно разобраться и потом раскомментировать
			// этот where() от вебинаров ломает всё расписание
            //$select->where("moderator = " . $this->getService('User')->getCurrentUserId() . ' OR ' . "moderator = " . $this->getService('User')->getCurrentUserId());
            //$select->where('moderator = ?', $this->getService('User')->getCurrentUserId());
        }
        $grid = $this->getGrid($select,
            array(
                'sort_order' => array('order' => true,'hidden' => true),
            	'TypeID2' => array('hidden' => true),
                'meeting_id' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'typeID' => array('title' => _('Тип')),
                'classifiers' => array('title' => _('Классификатор')),
                'begin' => array('title' => _('Ограничение по времени')),
                'condition' => array('title' => _('Условие')),
                'end' => array('hidden' => true),
                'timetype' => array('hidden' => true),
                'cond_project_id' => array('hidden' => true),
                'cond_mark' => array('hidden' => true),
                'cond_avgbal' => array('hidden' => true),
                'cond_sumbal' => array('hidden' => true),
                'cond_progress' => array('hidden' => true),
                'isfree' => array('hidden' => true),
            ),
            array(
                'title' => null,
                'typeID' => array('values' => HM_Event_EventModel::getMeetingTypes(false)),
                'begin' => array('render' => 'DateTimeStamp'),
                'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие')))
            )

        );

        $grid->updateColumn('typeID',array('searchType'=>'='));
        $grid->addAction(
            array('module' => 'meeting', 'controller' => 'result', 'action' => 'index', 'preview' => 1),
            array('meeting_id'),
            _('Просмотр результатов')
        );

        $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                      'params'   => array('{{TypeID2}}')
                )
            );

        $grid->addAction(array(
            'module' => 'meeting',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('meeting_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'meeting',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('meeting_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных мероприятий? Если мероприятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем участникам конкурса в меню <Материалы конкурса>.'));

        $grid->updateColumn('typeID',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTypeString'),
                    'params' => array('{{typeID}}')
                )
            )
        );

        $grid->updateColumn('begin',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getDateTimeString'),
                    'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
                )
            )
        );

        $grid->updateColumn('title',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{title}}', '{{meeting_id}}', '{{typeID}}')
                )
            )
        );

        $grid->updateColumn('condition',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getConditionString'),
                    'params' => array('{{cond_project_id}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
                )
            )
        );

        $grid->addMassAction(
            array(
                'module' => 'meeting',
                'controller' => 'list',
                'action' => 'export-variants',
            ),
            _('Cгенерировать варианты теста')
        );
        $grid->addSubMassActionInput(array(
            $this->view->url(array('action' => 'export-variants'))
        ),
            'variant_count'
        );


// exit($select->__toString());

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

        //Zend_Session::namespaceUnset('multiform');
        /*
        $projectId = (int) $this->_getParam('project_id', 0);
        $paginator = $this->getService('Meeting')->getPaginator(
           'project_id = '.$projectId.' AND timetype = 0',
           'begin'
        );

        $this->view->paginator = $paginator;
         *
         */
    }


    public function updateActions($typeID, $actions) {
        $meeting = HM_Meeting_MeetingModel::factory(array('typeID' => $typeID));

        if(!$meeting) return $actions;

        if ( $meeting->isResultInTable() ) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            unset($tmp[1]);
            return implode('<li>', $tmp);
        }
    }

    public function saveOrderAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        $order = $this->_getParam('posById', array());
        foreach($order as $key => $meeting){
            $res = $this->getService('Meeting')->updateWhere(array('order' => $key), array('meeting_id = ?' => $meeting));
            if($res === false){
               echo Zend_Json_Encoder::encode(array('result' => false));
               exit;
            }
        }
        echo Zend_Json_Encoder::encode(array('result' => true));
    }


    public function getConditionString($condmeeting_id, $condMark, $condProgress, $condAvg, $condSum)
    {
        $conditions = HM_Meeting_MeetingModel::getConditionTypes();
        if ($condmeeting_id > 0) {
            return $conditions[HM_Meeting_MeetingModel::CONDITION_MEETING];
        }
        if ($condProgress > 0) {
            return $conditions[HM_Meeting_MeetingModel::CONDITION_PROGRESS];
        }
        if ($condAvg > 0) {
            return $conditions[HM_Meeting_MeetingModel::CONDITION_AVGBAL];
        }
        if ($condSum > 0) {
            return $conditions[HM_Meeting_MeetingModel::CONDITION_SUMBAL];
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
        switch($timetype) {
            case 1:
                if (($end == 0) || ($begin == 0)) {
                	$beginOrEnd = ($begin == 0) ? $end : $begin;
                	return sprintf(_('%s-й день'), floor($beginOrEnd / 60 /60 /24));
                } elseif ($begin != $end) {
                    return sprintf(_('%s-й день - %s-й день'), floor($begin / 60 /60 /24), floor($end / 60 /60 /24));
                } else {
                    return sprintf(_('%s-й день'), floor($begin / 60 /60 /24));
                }
                break;
            case 2:
                return _('Без ограничений');
                break;
            default:
                $begin = new HM_Date($begin);
                $end = new HM_Date($end);
                return sprintf('%s - %s', $begin->get(Zend_Date::DATETIME_SHORT), $end->get(Zend_Date::DATETIME_SHORT));
                break;
        }
    }

    public function myAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $project = $this->getService('Project')->getOne($this->getService('Project')->find($projectID));

//         if($this->_project->access_mode == HM_Project_ProjectModel::MODE_FREE){
//             $this->_redirector->gotoSimple('card', 'index', 'project', array('project_id' => $projectId));
//         }
        $participant = false;

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $participant = $this->getService('User')->getCurrentUserId();

//            $this->view->setHeaderOptions(array(
//                    'pageTitle' => _('План мероприятий!'),
//                    'panelTitle' => $this->view->getPanelShortname(array('project' => $this->_project, 'projectName' => 'project')),
//            ));
        }

//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR)) {
//            $participant = ($this->_getParam('user_id',0))? (int) $this->_getParam('user_id'): false;
//            if (count($user = $this->getService('User')->find($participant))) {
//                $this->view->setSubHeader($user->current()->getName());
//            }
//        }
/*        $meetings = $this->getService('Meeting')->fetchAllDependenceJoinInner(
            'Assign',
            $this->getService('Meeting')->quoteInto(array('project_id = ?', ' AND MID = ? '), array($projectId, $this->getService('User')->getCurrentUserId())),
            'begin'
        );*/

/*        if($this->_project->access_mode == HM_Project_ProjectModel::MODE_FREE){
            $courses = $this->getService('Project')->getCourses($projectId);
            if(count($courses) != 0){
                $course =  $courses[0];
                $this->_redirector->gotoSimple('index', 'index', 'project', array('project_id' => $projectId, 'course_id' => $course->project_id));
            }


            $resources = $this->getService('Resource')->fetchAllDependenceJoinInner(
                'ProjectAssign','ProjectAssign.project_id = '. (int)$project->subid
            );
            if(count($resources) != 0){
                $resource =  $resources[0];
                $this->_redirector->gotoSimple('resource-view', 'index', 'project', array('project_id' => $projectId, 'course_id' => $resource->resource_id));
            }

            $this->_redirector->gotoSimple('index', 'index', 'project', array('project_id' => $projectId, 'course_id' => 0));

        }*/

        if($participant){
            $subSelect = $this->getService('Meeting')->getSelect();
            $subSelect->from(array('ul' => 'meetingsID'),'meeting_id')
                      ->where('MID=?',$participant);
            $addingWhere = array('meeting_id IN ?' => $subSelect);
        } else {
            $addingWhere = array();
/*

        $where = $this->getService('Meeting')->quoteInto(array('CID  = ?', ' AND typeID NOT IN (?)'), array($projectId, array_keys(HM_Event_EventModel::getExcludedTypes())));
        if ($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_MODERATOR) {
            $where = $this->getService('Meeting')->quoteInto(array('CID  = ?', ' AND typeID NOT IN (?)', ' AND (moderator = ?', ' OR moderator = ?)'), array($projectId, array_keys(HM_Event_EventModel::getExcludedTypes()), $this->getService('User')->getCurrentUserId(), $this->getService('User')->getCurrentUserId()));
        }
*/
        }
        $meetings = $this->getService('Meeting')->fetchAllDependence(
        		array('Assign', 'Moderator'),
        		array(
        			'project_id = ?' => $projectId,
                    'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
    		        'isfree = ?' => HM_Meeting_MeetingModel::MODE_PLAN,
        		) + $addingWhere,
        		array('order')
        );


        $titles = $meetings->getList('meeting_id', 'title');


        $meetingsPercent = 0;
		foreach ($meetings as $meeting){
			$meetingsArr[$meeting->meeting_id] = $meeting;

          //  $params = $meeting->getParams();
        //    $meetingsPercent += (int)$this->getService('Meeting')->getTotalCoursePercent($meeting->meeting_id, $this->getService('User')->getCurrentUserId(), $params['module_id']);
		}

        $percent = count($meetingsArr) ? $meetingsPercent / count($meetingsArr) : 0;

		$meetings = $meetingsArr;

        /*$percent = 0;
        $scoreMeetingsTotal = $this->getService('Meeting')->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Meeting')->quoteInto(array('project_id = ? AND vedomost = 1 ', ' AND MID = ?'), array($projectId, ($participant)? $participant : $this->getService('User')->getCurrentUserId()))
        );

        $scoreMeetingsScored = $this->getService('Meeting')->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Meeting')->quoteInto(array('project_id = ? AND vedomost = 1 ', ' AND MID = ? AND V_STATUS > -1'), array($projectId, ($participant)? $participant : $this->getService('User')->getCurrentUserId()))
        );

        $percent = ($scoreMeetingsTotal)? floor(($scoreMeetingsScored / $scoreMeetingsTotal) * 100) : 0;*/

        //$percent = $this->getService('Project')->getUserProgress($projectId, ($participant)? $participant : $this->getService('User')->getCurrentUserId());

       /* $collection = $this->getService('ProjectMark')->fetchAll(
            $this->getService('ProjectMark')->quoteInto(
                array('project_id = ?', ' AND MID = ?'),
                array($projectId, ($participant)? $participant : $this->getService('User')->getCurrentUserId())
            )
        );*/
        //$this->view->mark = count($collection) ? $this->getOne($collection)->mark : HM_Scale_Value_ValueModel::VALUE_NA;

		$this->view->titles = $titles;
        $this->view->markDisplay = (boolean) $participant;/*($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT) ||
                                   ($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_MODERATOR && $this->_getParam('user_id',0));*/
        $this->view->percent = (int) $percent;
        $this->view->meetings = $meetings;
        $this->view->project = $this->_subject;

//        @todo: Сделать нормальный view и отображать с делением на sections
//        $titles = array();
//        $this->view->sections = $this->getService('Section')->getSectionsMeetings($projectId, $addingWhere, $titles);
//        $this->view->titles = $titles;
//        $this->view->isEditSectionAllowed = $this->getService('Acl')->isCurrentAllowed('mca:meeting:list:edit-section');
        $this->view->forParticipant = $participant;
    }

    public function newAction()
    {
        $this->view->setHeaderOptions(array(
                'pageTitle' => _('План мероприятий'),
                //'panelTitle' => $this->view->getPanelShortname(array('project' => $this->_project, 'projectName' => 'project')),
            ));
        if (isset($_POST['questions_by_theme'])) {
            if (is_array($_POST['questions_by_theme']) && count($_POST['questions_by_theme'])) {
                $_POST['questions_by_theme'] = serialize($_POST['questions_by_theme']);
            }
        }

        $projectId = (int) $this->_getParam('project_id', 0);
        $request = $this->getRequest();

        $form = new $this->_formName();

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $checkResult = $this->checkMeetingDates($form,$projectId);
            $this->addMeeting($form);
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->projectId = $form->getValue('project_id');
                return true;
            } else {
                $extraMsg = (in_array($form->getValue('event_id'), HM_Meeting_MeetingModel::getTypesFreeModeEnabled())) ? _('Закрыт свободный доступ к материалам курса, использованным в данном занятии') : '';
                $this->_flashMessenger->addMessage(_('Мероприятие успешно добавлено. ') . $extraMsg);
                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения мероприятия была скорректирована так как она выходила за рамки курса'));
                }
                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));
            }
        } else {
            if ($questId = $this->_getParam('quest_id', 0)) {
                $form->setDefault('module', $questId);
            }

            $form->setDefault('project_id', $projectId);
            $form->setDefault('GroupDate', HM_Meeting_MeetingModel::TIMETYPE_FREE);
        }

        $this->view->form = $form;
        $this->view->project = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));
    }

    /**
     * #7590
     * Проверка дат при создании-обновлении занятия.
     * Если курс с регламентированными датами и правит-создает занятие не автор курса,
     * то не даем выскочить за рамки курса
     * @param $form
     * @param $projectId
     * @return bool - были или нет внесены изменения в даты занятия (TRUE-были)
     */
    private function checkMeetingDates($form, $projectId)
    {
        $projectService = $this->getService('Project');
        $project = $projectService->getOne($projectService->find($projectId));
        $result = FALSE;
        if ( $project ) {
            if ($project->period == HM_Project_ProjectModel::PERIOD_DATES /*&& $project->author_id != $this->getService('User')->getCurrentUserId()*/) {
                $beginProject = strtotime($project->begin);
                $endProject   = strtotime($project->end);

                if ($beginProject || $endProject) {
                if ($form->getValue('beginDate') && $form->getValue('endDate')) {
                    $beginMeeting  = strtotime($form->getValue('beginDate'));
                    $endMeeting    = strtotime($form->getValue('endDate'));

                    if ($project->begin && ($beginMeeting - $beginProject) < 0 || ($endProject - $beginMeeting) < 0 ) {
                            $date = new HM_Date($beginProject);
                            $form->getSubForm('step1')->getElement('beginDate')->setValue($date->get(Zend_Date::DATETIME));
                        $result = true;
                    }

                    if ($project->end && ($endProject - $endMeeting) < 0 || ($endMeeting - $beginProject) < 0 ) {
                            $date = new HM_Date($endProject);
                            $form->getSubForm('step1')->getElement('endDate')->setValue($date->get(Zend_Date::DATETIME));
                        $result = true;
                    }
                }

                if ($form->getValue('currentDate')) {
                     $curMeeting  = strtotime($form->getValue('currentDate'));
                         if ($beginProject && ($curMeeting - $beginProject) < 0 || ($endProject - $curMeeting) < 0 ) {
                             $date = new HM_Date($beginProject);
                             $form->getSubForm('step1')->getElement('currentDate')->setValue($date->get(Zend_Date::DATETIME));
                        $result = true;
                     }
                 }

                }

            }
        }

        return $result;
    }
    public function editAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $meetingId = (int) $this->_getParam('meeting_id', 0);
        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
        if ($meeting) {
            $this->view->setSubHeader($meeting->title);
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Мероприятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));
        }

        $form   = new $this->_formName();
        $params = $meeting->getParams();

        if ( $this->_getParam('fromlist',false) ) {
            $form->getSubForm('step1')
                 ->getElement('cancelUrl')
                 ->setValue($this->view->url(array('module' => 'meeting', 'controller' => 'list', 'action' => 'my', 'project_id' => $this->_getParam('project_id', 0),'user_id' => intval($this->_getParam('user_id',null))), null, true) . '#meeting_' . $meetingId);
            if ( $this->_getParam('user_id',false) ) {
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
            $checkResult = $this->checkMeetingDates($form,$projectId);

            $classifiers = $form->getClassifierValues();
            $this->getService('Classifier')->unlinkItem($meetingId, HM_Classifier_Link_LinkModel::TYPE_MEETING);
            if (is_array($classifiers) && count($classifiers)) {
                foreach($classifiers as $classifierId) {
                    if ($classifierId > 0) {
                        $this->getService('Classifier')->linkItem($meetingId, HM_Classifier_Link_LinkModel::TYPE_MEETING, $classifierId);
                    }
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
            $moduleId = $form->getValue('module');
            if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
                $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
                $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
            }
            $this->getService('Meeting')->setMeetingFreeMode($moduleId, $typeId, $projectId, HM_Meeting_MeetingModel::MODE_FREE_BLOCKED);

            $meeting = $this->getService('Meeting')->update(
                array(
                    'meeting_id'         => $form->getValue('meeting_id'),
                    'title'         => $form->getValue('title'),
                    'project_id'           => $form->getValue('project_id'),
                    'typeID'        => $form->getValue('event_id'),
                    'vedomost'      => $form->getValue('vedomost'),
                    'moderator'       => $form->getValue('moderator'),
                    'moderator' => $form->getValue('moderator'),
                    'createID'      => $this->getService('User')->getCurrentUserId(),
                    'recommend'     => $form->getValue('recommend'),
                    'all'           => $form->getValue('all'),
                    'GroupDate'     => $form->getValue('GroupDate'),
                    'beginDate'     => $form->getValue('beginDate'),
                    'endDate'       => $form->getValue('endDate'),
                    'currentDate'   => $form->getValue('currentDate'),
                    'beginTime'     => $form->getValue('beginTime'),
                    'endTime'       => $form->getValue('endTime'),
                    'beginRelative' => ($form->getValue('beginRelative'))? $form->getValue('beginRelative') : 1,
                    'endRelative' =>($form->getValue('endRelative'))? $form->getValue('endRelative') : 1,
                    'Condition'     => $form->getValue('Condition'),
                    'cond_project_id'    => $form->getValue('cond_project_id'),
                    'cond_mark'     => ((null !== $form->getValue('cond_mark')) ? $form->getValue('cond_mark') : ''),
                    'cond_progress' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_progress'): 0),
                    'cond_avgbal'   => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_avgbal') : 0),
                    'cond_sumbal'   => ((null !== $form->getValue('cond_sumbal')) ? $form->getValue('cond_sumbal') : 0),
                    'gid'           => $form->getValue('subgroups'),
                	'notice'        => $form->getValue('notice'),
                	'notice_days'   => (int) $form->getValue('notice_days'),
                    'activities'    => $activities,
                    'descript'      => $form->getValue('descript'),
                    'tool'          => $tool
                )
            );

            if ($meeting) {

                if($form->getValue('module')) {
                    $params['module_id'] = $form->getValue('module');
                }

                if($form->getValue('assign_type')) {
                    $params['assign_type'] = $form->getValue('assign_type');
                } elseif (isset($params['assign_type']) && $params['assign_type']) {
                    unset($params['assign_type']);
                }

                if($form->getValue('is_hidden',0)) {
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

                if ($form->getValue('mode_self_test')) {
                    $params['mode_self_test'] = $form->getValue('mode_self_test');
                }

                if ($meeting->getType() == HM_Event_EventModel::TYPE_TEST && $moduleId && $params['mode_self_test']) {
                    $this->getService('Quest')->update(
                        array(
                            'quest_id' => $moduleId,
                            'mode_self_test' => $params['mode_self_test']
                        )
                    );
                }

                if ($form->getValue('event_id') == HM_Event_EventModel::TYPE_LECTURE) {
                    $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
                }

                $meeting->setParams($params);

                $this->getService('Meeting')->update($meeting->getValues());

                $participants = $form->getValue('participants');

                $groupId = (int) $form->getValue('subgroups');

                if($groupId > 0){
                    $this->getService('Meeting')->unassignParticipant($meeting->meeting_id, $participants);

                    $participants = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                    $res = array();
                    foreach($participants as $value){
                        $res[] = $value->mid;
                    }
                    $participants = $res;
                }

                if (!$form->getValue('switch')) {
                    $participants = $meeting->getService()->getAvailableParticipants($projectId);
                }

                if (is_array($participants)) {
                    if($form->getValue('moderator') !== null)
                        $participants[] = $form->getValue('moderator');
                    $participants[] = $form->getValue('moderator');
                    $participants = array_unique($participants);
                }
                else {
                    $participants = array($form->getValue('moderator'), $form->getValue('moderator'));
                }

                $userVariants = array_filter($form->getValue('user_variant', array())); // filter_empty

                if ($form->getValue('assign_type', HM_Meeting_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Meeting_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                    $participants = array_keys($userVariants);
                }

                //$this->getService('Meeting')->assignParticipants($meeting->meeting_id, $participants);
                if ((($this->_project->period_restriction_type != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL)
                    || ($this->_project->state == HM_Project_ProjectModel::STATE_ACTUAL )))
                {
                    $this->assignParticipants($meeting->meeting_id, $participants, $userVariants);
                }
                $this->_postProcess($meeting, $form, $participants);

            }
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->cancelUrl = $form->getValue('cancelUrl');
                return true;
            } else {
                $this->_flashMessenger->addMessage(_('Занятие успешно изменено'));
                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                }

                if ( $form->getValue('fromList') ) {
                    $url = array(
                            'module'     => 'meeting',
                            'controller' => 'list',
                            'action'     => 'my',
                            'project_id' => $meeting->project_id
                    );
                    if ($form->getValue('fromList') == strval(intval($form->getValue('fromList')))) {
                        $url['user_id'] = $form->getValue('fromList');
                    }
                    $this->_redirector->gotoUrl($this->view->url($url, null,true) . '#meeting_' . $meetingId);
                }

                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $meeting->project_id));
            }
        } else {
            if ($meetingId) {
                if ($meeting) {
                    $values = array(
                        'meeting_id'       => $meeting->meeting_id,
                        'title'           => $meeting->title,
                        'project_id'      => $meeting->project_id,
                        'event_id'        => $meeting->typeID,
                        'vedomost'        => $meeting->vedomost,
                        'moderator'         => $meeting->moderator,
                        'moderator' => $meeting->moderator,
                        'recommend'       => $meeting->recommend,
                        'all'             => $meeting->all,
                        'module'          => $meeting->getModuleId(),
                        'formula'         => $meeting->getFormulaId(),
                        'formula_group'   => $meeting->getFormulaGroupId(),
                        'formula_penalty' => $meeting->getFormulaPenaltyId(),
                        'cond_project_id'      => $meeting->cond_project_id,
                        'cond_mark'       => $meeting->cond_mark,
                        'cond_progress'   => $meeting->cond_progress,
                        'cond_avgbal'     => $meeting->cond_avgbal,
                        'cond_sumbal'     => $meeting->cond_sumbal,
                        'gid'             => $meeting->gid,
                        'notice'          => $meeting->notice,
                        'notice_days'     => $meeting->notice_days,
                        'descript'        => $meeting->descript,
                        'assign_type'     => (isset($params['assign_type']))? (int) $params['assign_type'] : HM_Meeting_Task_TaskModel::ASSIGN_TYPE_RANDOM,
                    );

                    if ($meeting->activities && strlen($meeting->activities)) {
                        $values['activities'] = unserialize($meeting->activities);
                    }

                    if ($meeting->cond_project_id) {
                        $values['Condition'] = HM_Meeting_MeetingModel::CONDITION_MEETING;
                    }

                    if ($meeting->cond_progress) {
                        $values['Condition'] = HM_Meeting_MeetingModel::CONDITION_PROGRESS;
                    }

                    if ($meeting->cond_avgbal) {
                        $values['Condition'] = HM_Meeting_MeetingModel::CONDITION_AVGBAL;
                    }

                    if ($meeting->cond_sumbal) {
                        $values['Condition'] = HM_Meeting_MeetingModel::CONDITION_SUMBAL;
                    }

                    if ($meeting->gid != 0 && $meeting->gid != -1) {
                        $values['switch'] = 2;
                    } else {
                        $values['switch'] = 1;
                    }

                    switch($meeting->timetype) {
                        case HM_Meeting_MeetingModel::TIMETYPE_RELATIVE:
                            $values['GroupDate'] = HM_Meeting_MeetingModel::TIMETYPE_RELATIVE;
                            $values['beginRelative'] = floor($meeting->startday / 24 / 60 / 60);
                            $values['endRelative'] = floor($meeting->stopday / 24 / 60 / 60);
                            break;
                        case HM_Meeting_MeetingModel::TIMETYPE_FREE:
                            $values['GroupDate'] = HM_Meeting_MeetingModel::TIMETYPE_FREE;
                            break;
                        default:
                            $values['beginDate'] = $meeting->getBeginDate();
                            $values['endDate'] = $meeting->getEndDate();
                            $values['GroupDate'] = HM_Meeting_MeetingModel::TIMETYPE_DATES;
                            if ($values['beginDate'] == $values['endDate']) {
                                $values['GroupDate'] = HM_Meeting_MeetingModel::TIMETYPE_TIMES;
                                $values['currentDate'] = $values['beginDate'];
                                $values['beginTime'] = $meeting->getBeginTime();
                                $values['endTime'] = $meeting->getEndTime();
                                unset($values['beginDate']);
                                unset($values['endDate']);
                            }
                            break;
                    }

                    switch($meeting->getType()) {
                        case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                        case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                        case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT:
                        case HM_Event_EventModel::TYPE_TASK:
                        case HM_Event_EventModel::TYPE_POLL:
//                        case HM_Event_EventModel::TYPE_TEST:
                            $test = $this->getOne($this->getService('Test')->fetchAll(
                                $this->getService('Test')->quoteInto('meeting_id = ?', $meeting->meeting_id)
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
                                $values['threshold'] = $test->threshold;

                                $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
                                    $this->getService('TestTheme')->quoteInto(
                                        array('tid = ?', ' AND cid = ?'),
                                        array($test->tid, $test->cid)
                                    )
                                ));

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
                    if (($meeting->getType() == HM_Event_EventModel::TYPE_TEST ||
                            $formStep1Values['step1']['event_id'] == HM_Event_EventModel::TYPE_TEST) && ($questId !== null)) {
                        $values['module'] = $questId;
                    }
                    $form->setDefaults($values);
                    switch($meeting->getType()) {
                        case HM_Event_EventModel::TYPE_LECTURE:
                            // Инициализация treeSelect
                            if ($form->getSubForm('step2')->getElement('module')) {
                                $parentId = 'course_'.$meeting->project_id;
                                $parent = $this->getService('Course')->getParentItem($meeting->getModuleId());
                                if ($parent) {
                                    $parentId = $parent->oid;
                                }
                                $form->getSubForm('step2')->getElement('module')->jQueryParams['itemId'] = $parentId;
                            }
                            break;
                    }

                }
            }
        }
        $this->view->form    = $form;
        $this->view->project = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));

    }

    public function redirectDialogAction()
    {
        $this->view->redirectUrl = $this->_getParam('redirectUrl');
    }

    public function deleteAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $meetingId = (int) $this->_getParam('meeting_id', 0);
        $switcher = $this->_getParam('switcher', 0);

        if ($meetingId) {
            $this->getService('Meeting')->delete($meetingId);
        }

        $this->_flashMessenger->addMessage(_('Занятие успешно удалено'));

        $params = array(
          'project_id' => $projectId
        );

        if ($switcher) {
          $params['switcher'] = $switcher;
        }

        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, $params);
    }

    public function deleteByAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);

        $meetingIds = $this->_request->getParam('postMassIds_grid');
        $meetingIds = explode(',', $meetingIds);

        if (is_array($meetingIds) && count($meetingIds)) {
            foreach($meetingIds as $id) {
                $this->getService('Meeting')->delete($id);
            }
        }

        $this->_flashMessenger->addMessage(_('Занятия успешно удалены'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));

    }

    public function updateName($field, $id, $type){

        if($type == HM_Event_EventModel::TYPE_COURSE){

            $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($id));

            $courseId = $meeting->getModuleId();

            $course = $this->getService('Course')->getOne($this->getService('Course')->find($courseId));

            if($course->new_window == 1){
                $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('project_id', 0), $courseId);
                if($itemId != false){
                    return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $field.'</a>';
                }
            }
        }

        $target = ($type == HM_Event_EventModel::TYPE_WEBINAR) ? ' target="_blank" ' : '';

//        $meeting = HM_Meeting_MeetingModel::factory(array('typeID' => $type));
//        if ( $meeting->isResultInTable() || $type == HM_Event_EventModel::TYPE_TASK) {
//        	// хак для тестов
//        	if ($type == HM_Event_EventModel::TYPE_TEST) {
//        		return '<a href="' . $this->view->url(array('module' => 'project', 'controller' => 'index', 'action' => 'result', 'meeting_id' =>$id, 'project_id' => $this->_getParam('project_id'))) . '">'. $field.'</a>';
//        	}
//
//            return '<a href="' . $this->view->url(array('module' => 'meeting', 'controller' => 'result', 'action' => 'index', 'meeting_id' =>$id, 'project_id' => $this->_getParam('project_id'))). '" title="' . _('Просмотр общих результатов') . '">'. $field.'</a>';
//        }
        return '<a href="' . $this->view->url(array('module' => 'meeting', 'controller' => 'execute', 'action' => 'index', 'subjecttype' => 'project',  'meeting_id' => $id, 'project_id' => $this->_getParam('project_id'))). '" title="' . _('Просмотр занятия') . '"'. $target . '>'. $field.'</a>';
    }

    private function _postProcess(HM_Meeting_MeetingModel $meeting, Zend_Form $form)
    {
        switch($meeting->getType()) {
            case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
            case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT:
            case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                $abstract = $this->getOne($this->getService('Poll')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $meeting, $form);
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $abstract = $this->getOne($this->getService('Task')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $meeting, $form);
                break;
            case HM_Event_EventModel::TYPE_TEST:
            case HM_Event_EventModel::TYPE_POLL:
                /** @var HM_Quest_QuestModel $quest */
                $quest = $this->getOne($this->getService('Quest')->find($form->getValue('module')));
                $this->_postProcessQuest($quest, $meeting, $form);
                break;
            default:
                //$this->getService('Test')->deleteBy($this->getService('Test')->quoteInto('meeting_id = ?', $meeting->meeting_id));

                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$meeting->typeID])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($meeting->typeID);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onLessonUpdate($meeting, $form,'project');
        }
                    }
                }
        }
    }

    /**
     * @param HM_Quest_QuestModel $quest
     * @param HM_Meeting_MeetingModel $meeting
     * @param Zend_Form $form
     */
    private function _postProcessQuest($quest, $meeting, $form) {
        // Будем копировать настройки из области видимости курса
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL);

        /** @var HM_Quest_Settings_SettingsService $questSettingsService */
        $questSettingsService = $this->getService('QuestSettings');

        // Устанавливаем свою область видимости для занятий
        /** @var HM_Quest_Settings_SettingsModel $settings */
        $settings = $questSettingsService->copyToScope($quest, HM_Quest_QuestModel::SETTINGS_SCOPE_MEETING, $meeting->meeting_id);

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

    private function _postProcessTest($abstractTest, HM_Meeting_MeetingModel $meeting, Zend_Form $form)
    {
        if ($abstractTest) {

            $test = $this->getOne($this->getService('Test')->fetchAll(
                $this->getService('Test')->quoteInto(
                    array('meeting_id = ?', ' AND test_id = ?'),
                    array($meeting->meeting_id, $form->getValue('module'))
                )
            ));

            if ($test) {
                // assign values
                $test->test_id = $form->getValue('module');
                $test->title = $meeting->title;
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
                        array('meeting_id = ?'),
                        array($meeting->meeting_id)
                    )
                );
                $test = $this->getService('Test')->insert(
                    array(
                        'cid' => $meeting->project_id,
                        'datatype' => 1,
                        'sort' => 0,
                        'free' => 0,
                        'rating' => 0,
                        'status' => 1,
                        'last' => 0,
                        'cidowner' => $meeting->project_id,
                        'title' => $meeting->title,
                        'data' => $abstractTest->data,
                        'meeting_id' => $meeting->meeting_id,
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


            if ($test) {

                $this->getService('TestTheme')->deleteBy(
                    $this->getService('TestTheme')->quoteInto(
                        array('tid = ?', ' AND cid = ?'),
                        array($test->tid, $test->cid)
                    )
                );

                if ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED) {
                    $this->getService('TestTheme')->insert(
                        array(
                            'tid' => $test->tid,
                            'cid' => $test->cid,
                            'questions' => $form->getValue('questions_by_theme')
                        )
                    );
                }

                $form->setDefault('module', $test->tid);
            }
        }
    }

    public function themesAction()
    {
        $meetingId = (int) $this->_getParam('meeting_id', 0);
        $testId = (int) $this->_getParam('test_id', 0);
        $projectId = (int) $this->_getParam('project_id', 0);

        $themes = array(_('Без темы') => 0);
        if ($testId) {
            $test = $this->getOne($this->getService('TestAbstract')->find($testId));
            if ($test) {
                $questions = $test->getQuestionsIds();
                if (count($questions)) {
                    $collection = $this->getService('Question')->fetchAll(
                        $this->getService('Question')->quoteInto('kod IN (?)', $questions),
                        'qtema'
                    );
                    if (count($collection)) {
                        foreach($collection as $question) {
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
            if ($meetingId) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                    $this->getService('Test')->quoteInto('meeting_id = ?', $meetingId)
                ));
                if ($test) {
                    $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
                        $this->getService('TestTheme')->quoteInto(
                            array('tid = ?', ' AND cid = ?'),
                            array($test->tid, $projectId)
                        )
                    ));
                    if ($theme) {
                        $questionsByThemes = $theme->getQuestionsByThemes();
                        if (is_array($questionsByThemes) && count($questionsByThemes)) {
                            foreach($questionsByThemes as $theme => $count) {
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

    public function orderSectionAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $sectionId = $this->_getParam('section_id', array());
        $materials = $this->_getParam('material', array());
        echo $this->getService('Section')->setMeetingsOrder($sectionId, $materials) ? 1 : 0;
    }

    public function exportVariantsAction()
    {
        //$this->getService('Quest')->exportVariants(array(560), 2);
        $projectId = (int) $this->_getParam('project_id', 0);
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $variantCount = $this->_getParam('variant_count', 1);
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                try {
                    $pdfContent = $this->getService('Quest')->exportVariants($ids, $variantCount, 'meeting');
                } catch (Exception $exc) {
                    $this->_flashMessenger->addMessage(array('message' => _($exc->getMessage()), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));
                }

                if ($pdfContent) {
                    header('Content-type: application/dpf');
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
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('project_id' => $projectId));

    }


}
