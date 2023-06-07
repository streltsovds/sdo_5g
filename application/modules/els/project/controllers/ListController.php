<?php
class Project_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $classifierCache = array();
    protected $sessionsCache = array();

    protected $_projectId = 0;
    protected $_project = null;

    public function init() {

        $form = new HM_Form_Projects();

        $this->_projectId = (int) $this->_getParam('project_id', 0);

        if ($this->_projectId > 0) {
            $this->_project = $this->getOne(
                $this->getService('Project')->find($this->_projectId)
            );
            if($this->getRequest()->getActionName() != 'description'){
                $this->view->setExtended(
                    array(
                        'subjectName' => 'Project',
                        'subjectId' => $this->_projectId,
                        'subjectIdParamName' => 'project_id',
                        'subjectIdFieldName' => 'projid',
                        'subject' => $this->_project
                    )
                );
            }
            $this->_setParam('projid', $this->_projectId);

            $form->setDefault('cancelUrl', $this->view->url(array('module' => 'project', 'controller' => 'index', 'action' => 'card', 'project_id' => $this->_projectId)));

        } else {
            $form->setDefault('period', HM_Project_ProjectModel::PERIOD_FREE);
        }

        if($this->_getParam('projid', 0) > 0){

            $projectId = (int) $this->_getParam('projid', 0) ;
            $model = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));
            $form->getElement('icon')->setOptions(array('project' => $model));
        }

        $this->_setForm($form);

        parent::init();

        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {
            $this->_helper->ContextSwitch()
                          ->setAutoJsonSerialization(true)
                          ->addActionContext('calendar', 'json')
                          ->addActionContext('save-calendar', 'json')
                          ->initContext('json');
        }
    }

    protected function _redirectToIndex()
    {
        if ($this->_projectId > 0) {
            $this->_redirector->gotoSimple('card', 'index', 'project', array('project_id' => $this->_projectId));
        }

        if ($this->_getParam('base_id', 0)) {
            $this->_redirector->gotoUrl($this->view->url(array('action' => 'index', 'controller' => 'list', 'module' => 'project', 'base' => HM_Project_ProjectModel::BASETYPE_SESSION, 'projid' => null)) . '/?page_id=m0607');
        }

        $this->_redirector->gotoSimple('index');
    }

    public function indexAction()
    {

        $baseType = $this->_getParam('base', 0);

        $switcher = $this->_getParam('switcher', '');

        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT
        ){
            $switcher = 'list';
        }elseif($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)){
            $switcher = 'index';
        }elseif($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER) && $switcher == ''){
            $switcher = 'list';
        }

        if($switcher && $switcher != 'index'){
        	$this->getHelper('viewRenderer')->setNoRender();
        	$action = $switcher.'Action';
			$this->$action();
			echo $this->view->render('list/'.$switcher.'.tpl');
			return true;
        }

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
            $this->_request->setParam('ordergrid', 'name_ASC');
        }
        $select = $this->getService('Project')->getSelect();

        $select->from(array('s' => 'projects'),
            array(
                'projid' => 's.projid',
                'name' => 's.name',
                'state'=> 's.state',
                'period_restriction_type' => 's.period_restriction_type',
                'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'type' => 's.type',
                'begin' => "CASE WHEN (s.period_restriction_type = " . HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.begin IS NULL) THEN s.begin ELSE s.begin END",
                'end' => "CASE WHEN (s.period_restriction_type = " . HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.end IS NULL) THEN s.end ELSE s.end END",
                'period' => 's.period',
                )
            )
            ->joinLeft(array('st' => 'Participants'), 'st.CID = s.projid', array('participants' => 'COUNT(DISTINCT st.mid)'))
            ->joinLeft(
                array('cl' => 'classifiers_links'),
                's.projid = cl.item_id AND cl.type = 0', // классификатор уч.курсов
                array()
            )
            ->group(array(
                's.projid',
                's.name',
                's.state',
                's.period_restriction_type',
                's.begin',
                's.begin',
                's.end',
                's.end',
                's.period',
                's.external_id',
                's.type',
            ));

        // если пользователь - модератор конкурсов, то 
//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR)) {
//            $select->joinInner(array('m' => 'moderators'), $this->quoteInto('m.project_id = s.projid AND m.user_id = ?', $this->getService('User')->getCurrentUserId()), array());
//        }


        //Область ответственности
        $options = $this->getService('Curator')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
        if($options['unlimited_projects'] != 1 && $this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_CURATOR){
            $select->joinInner(array('d2' => 'curators'), 'd2.project_id = s.projid', array())
                   ->where('d2.MID = ?', $this->getService('User')->getCurrentUserId());
        }


        $url = array('module' => 'project', 'controller' => 'index', 'action' => 'card', 'project_id' => '{{projid}}');

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $select->joinInner(
                array('participants' => 'Participants'),
                's.projid = participants.CID',
                array()
            );
            $select->where('participants.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        $cardName = _('Карточка');

        $grid = $this->getGrid($select, array(
            'fixType' => array('hidden' => true),
            'state'    => array('hidden' => true),
            'period_restriction_type' => array('hidden' => true),
            'projid' => array('hidden' => true),
            'period' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'project_id' => '')) . '{{projid}}', $cardName) . ' <a href="' . $this->view->url($url, null, true, false) . '">{{name}}</a>'
            ),
            'begin' => array('title' => _('Дата начала'),
                'id' => 'dsad'
            ),
            'end' => array(
                'title' => _('Дата окончания')
            ),
            'participants' => array(
                'title' => _('Количество участников')
            ),
            'type' => array(
//                 'title' => _('Тип')
            		'hidden' => true
            ),
            'classifiers' => array(
                'title' => _('Классификация')
            ),
        ),
            array(
                'name' => null,
                'participants' => null,
                'begin' => array('render' => 'SubjectDate'),
                'end' => array('render' => 'SubjectDate'),
            	'type' => array('values' => HM_Project_ProjectModel::getTypes())
            )
        );

        $grid->addAction(array(
            'module' => 'project',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('projid'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'project',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('projid'),
            $this->view->svgIcon('delete', 'Удалить')
        );

//         $grid->addAction(array(
//             'module' => 'project',
//             'controller' => 'list',
//             'action' => 'copy'
//         ),
//             array('projid'),
//             _('Копировать')
//         );

        $grid->addMassAction(array(
            'module' => 'project',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );
        
        $grid->updateColumn('begin', array(
            'format' => array(
                'date',
                array('date_format' => HM_Locale_Format::getDateFormat())
            ),
            'callback' => array(
                'function' => array($this, 'updateDateBegin'),
                'params' => array('{{begin}}', '{{period}}', '{{period_restriction_type}}')
            )
        )
        );

        $grid->updateColumn('end', array(
            'callback' => array(
                'function' => array($this, 'updateDateEnd'),
                'params' => array('{{end}}', '{{period}}', '{{period_restriction_type}}')
            )
        )
        );

        $grid->updateColumn('type',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateType'),
                    'params'=> array('{{type}}')
                )
            )
        );

        $grid->updateColumn('participants',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateParticipants'),
                    'params'=> array('{{participants}}', '{{projid}}')
                )
            )
        );

        $grid->updateColumn('classifiers',
            array('callback' =>
                array('function' => array($this, 'classifiersCache'),
                      'params'   => array('{{classifiers}}', $select)
                )
            )
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{state}}', '{{period_restriction_type}}')
            )
        );

        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)
        ){
            $grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'projid');
            $grid->updateColumn('fixType', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateActions($state, $type, $actions)
    {

        if ($state == HM_Project_ProjectModel::STATE_CLOSED && $type == HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) {
            return $actions;
        }

        $actionUrl = $this->view->url(array('module' => 'project', 'controller' => 'index', 'action' => 'statement'));
        return $this->removeActionFromMenu($actions, $actionUrl);
        //return $actions;
    }

    public function calendarAction()
    {
        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            $begin = $this->getService('Project')->getDateTime(intval($this->_getParam('start')));
            $end   = $this->getService('Project')->getDateTime(intval($this->_getParam('end')));
            $where = $this->getService('Project')->quoteInto(array('base=?',' AND  NOT ( begin >= ?',' AND end <= ?)'),array(HM_Project_ProjectModel::BASETYPE_SESSION, $end, $begin));

            $collection    = $this->getService('Project')->fetchAllManyToMany('User','Teacher',$where);
            $eventsSources = $this->getService('Project')->getCalendarSource($collection, '0000ff', false, $this->_getParam('user_id', null));

            $where = $this->quoteInto(array('date >= ?',' AND date <= ?'), array($begin, $end));
            $holidays = $this->getService('Holiday')->fetchAll($where);
            if ( count($holidays) ) {
                foreach ($holidays as $day) {
                    $date = new HM_Date($day->date);
                    $eventsSources[] = array(
                        'id'    => $day->id,
                        'title' => $day->title,
                        'color' => "#c2c8d3",
                        'start' => $date->getTimestamp(),
                        'end'   => $date->getTimestamp(),
                        'editable' => false,
                        'borderColor' => '#ff0000'
                    );
                }
            }

            // добавляются произвольные мероприятия пользователей
            if (!$this->_getParam('no_user_events', false)) {
                if ($this->_getParam('user_id', null)) {
                    $where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id = ?'), array($begin, $end, $this->_getParam('user_id', null)));
                } else {
                    $where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id <> ?'), array($begin, $end, 0));
                }

                $holidays = $this->getService('Holiday')->fetchAllDependence('User', $where);
                if ( count($holidays) ) {
                    foreach ($holidays as $day) {
                        $date = new HM_Date($day->date);
                        $eventsSources[] = array(
                            'id'    => $day->id,
                            'title' => $day->title . ' ' . $day->users->current()->getName(),
                            'color' => "#c2c8d3",
                            'start' => $date->getTimestamp(),
                            'end'   => $date->getTimestamp(),
                            'editable' => false,
                            'borderColor' => '#00FF00'
                        );
                    }
                }
            }

            $this->view->assign($eventsSources);
        } else {
            $this->view->source = array('module'=>'project', 'controller'=>'list', 'action'=>'calendar', 'no_user_events' => 'y');
            $this->view->editable = ($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT) ? false: true;
        }
    }


    public function saveCalendarAction()
    {
        $projectId = $this->_getParam('eventid',0);
        $begin     = $this->_getParam('start',0);
        $end       = $this->_getParam('end',0);

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $projectId && $begin && $end) {

            $project = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));
            if ($project) {
                $data = array(
                    'projid' => $project->projid,
                    'begin' => $this->getService('Project')->getDateTime($begin/1000, true) . ' 00:00:00',
                    'end'   => $this->getService('Project')->getDateTime($end/1000, true) . ' 23:59:59'
                );
                $res = $this->getService('Project')->update($data);
                if ($res) {
                    $result = _('Данные успешно обновлены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }

    public function assignAction()
    {
        $mode = $this->_getParam('mode',false);
        switch ( $mode) {

            case 'users': //приаттачиваем пользователей к курсам
                          $users = $this->_getParam('usersId',array());
                          $projects = explode(',',$this->_getParam('postMassIds_grid',array()));

                          if ( $this->usersAssign($projects,$users) ) {
                              $this->_flashMessenger->addMessage(_('Участники успешно назначены'));
                          } else {
                              $this->_flashMessenger->addMessage(_('При назначении некоторых участников возникли ошибки'));
                          }
                          break;
            default:
                    $this->_flashMessenger->addMessage(_('Выбрано некорректное действие'));
                    break;
        }
        $this->_redirector->gotoSimple('index', 'list', 'project');
    }

    /**
     * приаттачиваем пользователей
     * @param int|array $projects 
     * @param int|array $users пользователи
     * @return boolean
     * @todo Эту и подобную функции прорефакторить
     */
    private function usersAssign($projects, $users)
    {
        if ( !$projects || !$users ) return false;

        $result = true;
        $projects = (array) $projects;
        $users    = (array) $users;

        $projectService = $this->getService('Project');
        $userService = $this->getService('User');

        foreach ( $projects as $project ) {
         if ( !count($projectService->find($project))) {
            $result = false;
            continue;
         }
         foreach ( $users as $user ) {
            // проверка существования пользователя
            if ( !count($userService->find($user)) ) {
                $result = false;
                continue;
            }
            // если пользователь не студент данного курса - делаем его таковым
            if ( !$projectService->isParticipant($project,$user) ) {
                $projectService->assignParticipant($project,$user);
            }
         }
        }
        return $result;
    }

    public function listAction()
    {
        $now = date('Y-m-d H:i:s');
        $listSwitcher = $this->_getParam('list-switcher', 'current');
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {

            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Project.begin > ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR ',
                            '(Project.begin > ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR ',
                            '(Project.state = ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Project_ProjectModel::STATE_PENDING, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Project.period = ?) OR ',
                            '(Project.begin < ?',' AND Project.end > ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR ',
                            '(Project.begin < ?',' AND Project.end > ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR ',
                            '(Project.state = ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR',
                            '(Project.period = ?',' AND self.end_personal > ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            HM_Project_ProjectModel::PERIOD_FREE,
                            $now, $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Project_ProjectModel::STATE_ACTUAL, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Project_ProjectModel::PERIOD_FIXED, $now,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Project.end < ?',' AND Project.period = ?',' AND Project.period_restriction_type = ?) OR',
                            '(Project.period = ?',' AND self.end_personal < ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Project_ProjectModel::PERIOD_FIXED, $now,
                        )
                    );
                    break;
            }
            
            $participants = $this->getService('Participant')->fetchAllDependenceJoinInner('Project', $where);
            
            
            $courses = $participants->getList('participant_id', 'CID');
            
            if ($listSwitcher == 'past') {

                $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId(), 'is_lookable = ?' => HM_Role_GraduatedModel::LOOKABLE));
                $graduatedCourses = $graduated->getList('SID', 'CID');
                $courses = array_merge($courses, $graduatedCourses);

                foreach ($graduated as $grad) {
                	$participantCourseData[$grad->CID] = array(
                		'begin' => $grad->begin,
                		'end' => $grad->end,
                	);
                }
            }
            
            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            '((begin > ?',' AND period = ?',' AND period_restriction_type = ?) OR ',
                            '(begin > ?',' AND period = ?',' AND period_restriction_type = ?) OR ',
                            '(state = ?',' AND period = ?',' AND period_restriction_type = ?))',
                        ),
                        array(
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Project_ProjectModel::STATE_PENDING, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            '((period = ?) OR ',
                            '(begin < ?',' AND end > ?',' AND period = ?',' AND period_restriction_type = ?) OR ',
                            '(begin < ?',' AND end > ?',' AND period = ?',' AND period_restriction_type = ?) OR ',
                            '(state = ?',' AND period = ?',' AND period_restriction_type = ?))',
                        ),
                        array(
                            HM_Project_ProjectModel::PERIOD_FREE,
                            $now, $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Project_ProjectModel::STATE_ACTUAL, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            '((end < ?',' AND period = ?',' AND period_restriction_type = ?))',
                        ),
                        array(
                            $now, HM_Project_ProjectModel::PERIOD_DATES, HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                        )
                    );
                    break;
                default:
                    $where = '1 = 0';
            }
            
            $where = 'is_public = 1 AND '.$where;
            
            $publicProjects = $this->getService('Project')->fetchAll($where)->getList('projid', 'projid');

            $courses = array_merge($courses, $publicProjects);

            $participantCourseData = array();
            foreach ($participants as $participant) {
            	$participantCourseData[$participant->CID] = array(
            		'begin' => $participant->time_registered,
            	);
            }

            if (count($courses)) {
                $in = implode(',', $courses);
                $projects = $this->getService('Project')->fetchAllManyToMany('User', 'Participant', 'projid IN (' . $in . ')', 'name');
            }
            $this->view->share = true; // allow facebook etc.

			$this->view->participantCourseData = $participantCourseData;
        }

        $this->view->is_participant = true;
        $this->view->listSwitcher = $listSwitcher;
        $this->view->projects = $projects;
    }



    protected function _getMessages() 
    {
        return array(
            self::ACTION_INSERT    => _('Конкурс успешно создан'),
            self::ACTION_UPDATE    => _('Конкурс успешно обновлён'),
            self::ACTION_DELETE    => _('Конкурс успешно удалён'),
            self::ACTION_DELETE_BY => _('Конкурсы успешно удалены')
        );
    }


    public function setDefaults(Zend_Form $form, $newSession = false) {

        $projectId = ( int ) $this->_request->getParam('projid', 0);

        $project = $this->getService('Project')->getOne($this->getService('Project')->find($projectId));
        if ($project) {

            if ($newSession) {
                $today = new HM_Date();
                $project->begin = $today->toString('dd.MM.Y');
                $today->add(10, HM_Date::DAY);
                $project->end = $today->toString('dd.MM.Y');
            } else {
                $project->begin = $project->getBegin();
                $project->end = $project->getEnd();
            }
            $values = $project->getValues();

            $accessElements = array();

            foreach(HM_Project_ProjectModel::getFreeAccessElements() as $key => $value){
                if($key & $values['access_elements']){
                    $accessElements[] = $key;
                }
            }
            $values['access_elements'] = $accessElements;


            if ($values['period_restriction_type'] == HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) {
                $begin = new HM_Date($values['begin']);
                $values['begin'] = $begin->toString('dd.MM.Y');
                $end = new HM_Date($values['end']);
                $values['end'] = $end->toString('dd.MM.Y');
            }
            $form->populate($values);
        }
    }

    public function update(Zend_Form $form) 
    {
        $accessElements = 7;//0
        $periodRestrictionType = $form->getValue('period_restriction_type');
        $project = $this->getService('Project')->update(
            array(
                'projid'                  => $form->getValue('projid'),
                'name'                    => $form->getValue('name'),
                'shortname'               => $form->getValue('shortname'),
                'description'             => $form->getValue('description'),
                'external_id'             => $form->getValue('external_id'),
                'code'                    => $form->getValue('code'),
                'type'                    => $form->getValue('type'),
                'begin'                   => ($periodRestrictionType != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
                'end'                     => ($periodRestrictionType != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
                'begin'           => $form->getValue('begin'),
                'end'             => $form->getValue('end'),
                'period'                  => $form->getValue('period'),
                'period_restriction_type' => $periodRestrictionType,
                'access_elements'         => $accessElements,
                'base_color'              => $form->getValue('base_color'),
                'is_public'               => $form->getValue('is_public')
            )
        );
        $projectId = $project->projid;
        //$this->getService('Project')->linkClassifiers($projectId, $form->getClassifierValues());
        $this->getService('Project')->updateIcon($projectId, $form->getElement('icon'));

        $protocol = $form->getElement('protocol');
        if ($protocol->isUploaded()){

            $pathinfo = pathinfo($protocol->getFileName());
            $project->protocol = $pathinfo['basename'];
            $this->getService('Project')->update($project->getValues());

            $path = Zend_Registry::get('config')->path->upload->project_protocols . '/' . $project->projid;
            $protocol->addFilter('Rename', $path, 'protocol', array( 'overwrite' => true));
            $protocol->receive();
        }

        //Обрезаем все занятия выходящие за рамки курса
        if( $project->period == HM_Project_ProjectModel::PERIOD_DATES ) {
            $lessonService = $this->getService('Lesson');
            $lessonService->updateWhere(array('end' => $form->getValue('end') . ' 23:59:59'),
                                        $lessonService->quoteInto(array('CID = ?',' AND (end > ?',' OR end < ?)'),
                                                                  array($projectId,
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('begin'))))));
            $lessonService->updateWhere(array('begin' => $form->getValue('begin') . ' 00:00:00'),
                                        $lessonService->quoteInto(array('CID = ?',' AND (begin > ?',' OR begin < ?)'),
                                                                  array($projectId,
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                                                                        $this->getService('Lesson')
                                                                             ->getDateTime(strtotime($form->getValue('begin'))))));
        }

    }

    public function delete($id) {
        $this->getService('Project')->delete($id);
    }

    public function create(Zend_Form $form) {

        $accessElements = 7; // 0

        $periodRestrictionType = $form->getValue('period_restriction_type');
        $project = $this->getService('Project')->insert(
            array(
                'name'                => $form->getValue('name'),
            	'shortname'           => $form->getValue('shortname'),
                'description'         => $form->getValue('description'),
                'external_id'         => $form->getValue('external_id'),
                'code'                => $form->getValue('code'),
                'type'                => $form->getValue('type'),
                'begin'               => ($periodRestrictionType != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
                'end'                 => ($periodRestrictionType != HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
                'begin'       => $form->getValue('begin'),
                'end'         => $form->getValue('end'),
                'period'              => $form->getValue('period'),
                'period_restriction_type' => $periodRestrictionType,
                'access_elements'     => $accessElements,
                'base_color'          => ($baseId && ($base == HM_Project_ProjectModel::BASETYPE_SESSION))?  $this->getService('Project')->getProjectColor($baseId) : $this->getService('Project')->generateColor(),
                'is_public'           => $form->getValue('is_public')
            )
        );

        // Добавление курсов для кураторов, наблюдающих за новыми курсами
        $curators = $this->getService('CuratorOptions')->fetchAll(array('unlimited_projects = ?' => 0, 'assign_new_projects = ?' => 1, 'user_id != ?' => $this->getService('User')->getCurrentUserId()));

        foreach($curators as $value){
            $this->getService('Curator')->insert(array('MID' => $value->user_id, 'project_id' => $project->projid));
        }

        $this->getService('Curator')->insert(array('MID' => $this->getService('User')->getCurrentUserId(), 'project_id' => $project->projid));

        $classifiers = $form->getClassifierValues();
        $this->getService('Classifier')->unlinkItem($project->projid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($project->projid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                }
            }
        }

        $this->getService('Project')->updateIcon($project->projid, $form->getElement('icon'));

        $protocol = $form->getElement('protocol');
        if ($protocol->isUploaded()){

            $pathinfo = pathinfo($protocol->getFileName());
            $project->protocol = $pathinfo['basename'];
            $this->getService('Project')->update($project->getValues());

            $path = Zend_Registry::get('config')->path->upload->project_protocols . '/' . $project->projid;
            $protocol->addFilter('Rename', $path, 'protocol', array( 'overwrite' => true));
            $protocol->receive();
        }
    }

    public function cardAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $projectId = (int) $this->_getParam('project_id', 0);
        $this->view->project = false;
        $this->view->project = $this->getService('Project')->getOne(
            $this->getService('Project')->find($projectId)
        );

    }

    public function descriptionAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $this->view->project = $this->getService('Project')->getOne(
            $this->getService('Project')->find($projectId)
        );

        $this->view->clType = $this->_getParam('type', 0);
        $this->view->clItem = $this->_getParam('item', 0);
        $this->view->clClassifierId = $this->_getParam('classifier_id', 0);

        $this->view->regText = _('Подать заявку');
    }



    public function updateType($type)
    {
        $types = HM_Project_ProjectModel::getTypes();
        return $types[$type];
    }

    public function updateParticipants($participants, $project_id)
    {
        if (!empty($participants)) {
            return '<a href="' . $this->view->url(array('module' => 'assign', 'controller' => 'participant', 'action' => 'index', 'project_id' => $project_id)) . '" title="' . _('Список участников') . '">' . $participants . '</a>';
        }
        return $participants;
    }

    public function updateDateBegin($date, $period, $periodRestrictionType)
    {
        switch ($period) {
        	case HM_Project_ProjectModel::PERIOD_FREE:
        		return _('Без ограничений');
        	case HM_Project_ProjectModel::PERIOD_FIXED:
        		return _('Дата регистрации на курс');
        	default:
                if ($periodRestrictionType == HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) {
                    $date .= HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Плановая дата. Фактически начало/окончание конкурса определяется менеджером'), 1);
                }
                return $date;
        }
    }

    public function updateDateEnd($date, $period, $periodRestrictionType)
    {
        $date = $this->getDateForGrid($date);
        switch ($period) {
        	case HM_Project_ProjectModel::PERIOD_FREE:
        		return _('Без ограничений');
        	default:
                if ($periodRestrictionType == HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL) {
                    $date .= HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Плановая дата. Фактически начало/окончание конкурса определяется менеджером'), 1);
                }
                return $date;
        }
    }

    public function classifiersCache($field, $select){

        if($this->classifierCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['classifiers'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->classifierCache = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp));
        }

        $fields = array_unique(explode(',', $field));
        $fields = array_filter($fields, array(get_class($this), '_filterCachedClassifiers'));
        $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount(count($fields)) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->classifierCache->exists('classifier_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode(' ',$result);
        else
            return _('Нет');
    }

    protected function _filterCachedClassifiers($id) {
        return $this->classifierCache->exists('classifier_id', $id);
    }

    public function editAction()
    {
        if ($projid = $this->_getParam('project_id')) {
            $this->_setParam('projid', $projid);
        }
        return parent::editAction();
    }

    public function copyAction()
    {
        $projid = (int) $this->_getParam('projid', 0);
        if ($projid) {
            $project = $this->getService('Project')->copy($projid);
            if ($project) {
                if($this->_form->hasModifier('HM_Form_Modifier_BaseTypeBase')){
                    $this->_flashMessenger->addMessage(_('Конкурс успешно скопирован.'));
                }else{
                    $this->_flashMessenger->addMessage(_('Конкурс успешно скопирован.'));
                }
            }
        }

        $this->_redirector->gotoSimple('index', 'list', 'project', array('switcher' => 'index', 'project_id' => $this->_getParam('project_id', null), 'base' => $this->_getParam('base', 0)));
        //$this->_redirectToIndex();
    }
}

