<?php
class Meeting_ResultController extends HM_Controller_Action_Project
{
    protected $service     = 'Project';
    protected $idParamName = 'project_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    private $_participantId = 0;
    //private $_project_id; nobody is need it
    private $_meeting = null;

    private $_maxScoreCache = null;

    public function init()
    {
        parent::init();
        if (
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT))
        ) {
            $this->_participantId = $this->getService('User')->getCurrentUserId();
        } else {
            $this->_participantId = $this->_getParam('user_id', 0);
        }
        $this->view->setHeader(_('Результаты занятия'));

        $this->view->disabledMods = array('skillsoft');
    }

    public function indexAction()
    {
        $this->_meeting = $this->getOne($this->getService('Meeting')->find((int) $this->_getParam('meeting_id', 0)));
        if ($this->_meeting) {
            $this->view->setSubHeader($this->_meeting->title);
        }

        if ($this->_meeting) {
            // не показываем переключатель
            $disabledMods = $this->view->disabledMods;
            $this->view->disabledMods = array('index','skillsoft','listlecture');
            switch($this->_meeting->getType()) {
                case HM_Event_EventModel::TYPE_RESOURCE:
                    $params = $this->_meeting->getParams();
                	$url = $this->view->url(array('module' => 'resource','controller' => 'index','action' => 'index','resource_id' => array('resource_id' => $params['module_id'])));
                	$this->_redirector->gotoUrl($url);
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_POLL:
                    $this->test();
                    break;
                 case HM_Event_EventModel::TYPE_COURSE:
                 case HM_Event_EventModel::TYPE_LECTURE:
                    if(!$this->_getParam('userdetail',false))
                    {
                        $this->courseMain();
                    }
                    else
                    {
                        // показываем переключатель
                        $this->view->disabledMods = $disabledMods;
                        $result = $this->course();
                        // if (empty($result))
                        //выводим результаты прохождения учебного модуля по умолчанию...
                        //    $this->defaultResult();
                    }
                    break;
                case HM_Event_EventModel::TYPE_TASK;
                    $this->task();
                    break;
                case HM_Activity_ActivityModel::ACTIVITY_FORUM:
                    $url = $this->_meeting->getResultsUrl();
                	$this->_redirector->gotoUrl($url);
                    break;
                default:
                    $feedback = HM_Event_EventModel::getExcludedTypes();
                    if(isset($feedback[$this->_meeting->typeID])) $this->pollLeader();
                    else $this->lecture();
            }
        }
        //$this->_flashMessenger->addMessage(array('message' => _('Для данного типа занятия подробная статистика отсутствует.'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        //$this->_redirector->gotoUrl($url);
    }
    //откат до выяснения 22.03.2013
    /**
     * метод выводит результаты прохождения учебного модуля
     * @author Glazyrin_Andrey <glazyrin.andre@mail.ru>
     * @date 14.01.2013
     */
    /*public function defaultResult()
    {
        $projectId = $this->_getParam('project_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();
        if($this->_getParam('progressgrid', '') != '' && strpos($this->_getParam('progressgrid', ''), '=') !==0){
            $this->_setParam('progressgrid', '=' . $this->_getParam('progressgrid', ''));
        }
        $select = $this->getService('Project')->getSelect();
        $columnOptions = array(
           'meeting_id' => array('hidden' => true),
           'MID' => array('hidden' => true),
           'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink(
                $this->view->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => '')).'{{MID}}',
                _('Карточка пользователя')).'{{fio}}'),
                'Title' => array(
                'title' => _('Название'),
            ),
            'typeID' => array(
                'hidden' => true,
                'title' => _('Тип'),
            ),
            'V_DONE' => array(
               'title' => _('Состояние'),
                   'callback' => array(
                       'function' => array($this, 'updateDoneStatus'),
                       'params' => array('{{V_DONE}}')
                   )
            ),
            'progress' => array(
               'title' => _('Результат'),
                   'callback' => array(
                       'function' => array($this, 'updateProgress'),
                       'params' => array('{{progress}}')
                   )
            ),
            'updated' => array(
                'title' => _('Дата последнего просмотра')
            ),
        );
        //для mssql т.к. при group по одному параметру не работает сколько полей столько и группируем
        //параметров
        $group = array(
                        'schid.MID',
                        'sch.meeting_id',
                        'sch.Title',
                        'sch.typeID',
                        'schid.V_STATUS',
                        'schid.V_DONE',
                        'schid.launched',
                        'schid.updated'
                      );
        $select->where('schid.MID = ?', $userId);
        unset($columnOptions['fio']);
        $select->from(array('schid' => 'meetingsID'), array('MID'))
            ->joinInner(array('sch' => 'meetings'),
                              'sch.meeting_id = schid.meeting_id',
                        array(
                                'meeting_id',
                                'Title',
                                'typeID',
                                'V_DONE'     => 'schid.V_DONE',
                                'progress'   => 'schid.V_STATUS',
                                'updated'=>'schid.updated'
                        ))
            ->where($this->quoteInto(array('sch.CID = ?'), array($projectId,0)))
            ->group($group);
        $people = $this->getService('User')->fetchAllJoinInner('Participant', 'Participant.CID = ' . (int) $projectId );
        $statuses = array('0' => _('Не начат'), '2' => _('Пройден'), '1' => 'В процессе');
        $filterOptions =  array(
               'V_DONE' => array('values' => $statuses),
        );
        if ( $this->_getParam('meeting_id',0) ) {
            $select->where('sch.meeting_id = ?', $this->_getParam('meeting_id',0)); // занятие
        } else {
            $select->where('sch.isfree = ?', HM_Meeting_MeetingModel::MODE_FREE);
        }
        $grid = $this->getGrid(
            $select,
            $columnOptions,
            $filterOptions,
           'grid'
       );
       $grid->updateColumn('Title',
                                 array(
                                       'callback' =>
                                        array(
                                            'function' => array($this, 'getTitleString'),
                                            'params' => array('{{Title}}','{{typeID}}')
                                        )
                                    )
                                );
        $grid->updateColumn('updated', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $this->view->grid = $grid;
    }
    public function updateDoneStatus($status)
    {
        if(!$status)     return _('Не начат');  // $status ==0 OR IS NULL
        if($status == 2) return _('Пройден');   // $status == 2

        return _('В процессе');                 // $status == 1
    }
    public function updateProgress($score)
    {
        if(empty($score) || $score < 0)
            return "Не пройден";
        return "Успешно пройден";
    }
    public function getTitleString($title,$typeID)
    {
        return '<span class="' . HM_Meeting_MeetingModel::getIconClass($typeID) . '">' . $title . '</span>';
    }
    */
    public function lecture()
    {
        $switcher = $this->_getParam('switcher', 0);
        if($switcher && $switcher != 'index'){
        	$this->getHelper('viewRenderer')->setNoRender();
        	$action = $switcher.'Action';
			$this->$action();
			$this->view->render('result/'.$switcher.'.tpl');
			return true;
        }

        $select = $this->getService('Meeting')->getSelect();
        $select->from(
                    array('l' => 'scorm_tracklog'),
                    array(
                        'l.trackID',
                        'MID' => 'l.mid',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        'item' => 'o.title',
                        'l.score',
                        'l.scoremax',
                        'l.scoremin',
                        'l.start',
                        'l.stop',
                        'l.status'
                    )
                )
                ->joinLeft(
                    array('p' => 'People'),
                    'p.MID = l.mid',
                    array()
                )->joinLeft(array('o' => 'organizations'), 'o.oid = l.ModID', array())
                ->where('l.meeting_id = ?', $this->_meeting->getMeetingId());

        if ($this->_participantId) {
            $select->where('l.mid = ?', $this->_participantId);
        }

        $grid = $this->getGrid($select,
            array(
                'trackID' => array('hidden' => true),
                'MID' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{MID}}') . '{{fio}}'),
                'item' => array('title' => _('Материал')),
                'score' => array('title' => _('Балл')),
                'scoremax' => array('title' => _('Мин/Mакс'), 'decorator' => '{{scoremin}}<br/>{{scoremax}}'),
                'scoremin' => array('hidden' => true),
                'start' => array('title' => _('Начало')),
                'stop' => array('title' => _('Конец')),
                'status' => array('title' => _('Статус'))
            ),
            array(
                'fio' => null,
                'item' => null,
                'score' => null,
                'scoremax' => null,
                'start' => array('render' => 'DateTimeStamp'),
                'stop' => array('render' => 'DateTimeStamp'),
                'status' => array('values' => HM_Scorm_Track_Data_DataModel::getStatuses())
            )
        );

        $grid->updateColumn('start', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $grid->updateColumn('stop', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTrackStatusString'),
                    'params' => array('{{status}}')
                )
            )
        );

        if ($this->_participantId) {
            $grid->updateColumn('fio', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }


    public function listlectureAction(){

        $this->_meeting = $this->getOne($this->getService('Meeting')->find((int) $this->_getParam('meeting_id', 0)));

        $this->view->setHeader(_('Результаты работы с учебным модулем') . HM_View_Helper_Footnote::marker(1));
        if ($this->_meeting) {
            if ($user = $this->getService('User')->find($this->_participantId)->current()) {
                $this->view->setSubHeader($this->_meeting->title . ' - ' . $user->getName());
            }
        }

        $items = array();
        if ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_meeting->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_meeting->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true);
            }
        }
                
        // вынес логику в ScormTrack
        list($itemResults, $fullProgress) = $this->getService('ScormTrack')->getAggregatedResults($courseId, $this->_meeting->getMeetingId(), $this->_participantId, $items);

        $this->view->items = $itemResults;
        $this->view->fullProgress = $fullProgress;
        $this->view->footnote(_('Отображается результат последней (хронологически) попытки'), 1);
    }

    public function courseMain()
    {
        $select = $this->getService('Meeting')->getSelect();

        //if ($this->_meeting->typeID == HM_Event_EventModel::TYPE_COURSE) {
        if ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_meeting->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_meeting->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = array($itemId); 
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
                  
            }
        }
        
        $subSelect = $this->getService('Meeting')->getSelect();
        $subSelect->from( array('l' => 'scorm_tracklog'),
                          array('l.mid',
                                'l.cid',
                                'count' => new Zend_Db_Expr('COUNT(trackID)'),
                                'mscore' => new Zend_Db_Expr('MAX(score)')) )
                  ->where( 'l.cid = ?', $courseId)
                  ->group( array('l.mid','l.cid') );

        if (count($items)) {
            $subSelect->where('l.ModID IN (?)', $items);
        }
        
        if ($this->_meeting) {
            $subSelect->where('l.meeting_id = ?', $this->_meeting->getMeetingId());
        }

        if ($this->_participantId) {
            $subSelect->where('l.mid = ?', $this->_participantId);
        }

        $select->from( array('t1' => 'Participants'),
                       array('t1.MID',
                             'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")) )
               ->joinLeft(array('p' => 'People'),
                           'p.MID = t1.MID',
                           array())
               ->joinLeft( array('t3' => $subSelect),
                           't1.MID = t3.mid',
                           array('t3.count', 't3.mscore'))
               ->joinLeft(array('les' => 'meetingsID'),
                          $this->getService('Meeting')->quoteInto('les.meeting_id = ? AND les.MID = t1.MID',$this->_meeting->getMeetingId()),
                          array('status' =>'les.V_DONE'))
               ->where('t1.CID = ?', $this->_getParam('project_id', 0));

        //exit($select->__toString());

       $columns = array('trackID' => array('hidden' => TRUE),
       					'MID' => array('hidden' => TRUE),
                        'fio' => array('title' => _('ФИО'),
                                       'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view',
                                       															   'controller' => 'list',
                                       															   'module' => 'user',
                                       															   'user_id' => '')) . '{{MID}}') .
                                       			      '{{fio}}'),
                        'status' => array('hidden' => true), // не будем показывать статус; с введением шкал 2- и 3-состояния поле статус потеряло смысл
       					'count' => array(
       					    'title' => _('Количество сеансов'),
       					    'decorator' => '<a href="' . $this->view->url(array('userdetail'=>'yes', 'user_id' => '')) . '{{MID}}' . '">{{count}}</a>'
   					    ),
                        'mscore' => array('title' => _('Балл')),);

       $filters = array('fio' =>NULL);

       $grid = $this->getGrid($select,$columns,$filters);

       $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getModuleStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

       $this->view->grid = $grid;
    }

    /**
     * текстовка для типа модуля
     * @param unknown_type $modId
     * @param unknown_type $count
     * @return string
     * @todo сделать корректное определение типа "завершен"
     */
    public function getModuleStatus($status)
    {
        return HM_Meeting_Assign_AssignModel::getProgressStatusName($status);
    }

    /**
     *  Максимальный результат материала выделяется стилем
     * @param $item int
     * @param $score int
     * @param $select Zend_Db_Select
     */
    public function updateScore($item, $score, $select) {
        if ($this->_maxScoreCache === null) {
            $this->_maxScoreCache = array();

            $result = $select->query()->fetchAll();

            if ($result) {
                foreach ($result as $val) {
                    if ((!isset($this->_maxScoreCache[$val['item']])) || (isset($this->_maxScoreCache[$val['item']]) && $this->_maxScoreCache[$val['item']] < $val['score']) ) {
                        $this->_maxScoreCache[$val['item']] = $val['score'];
                    }
                }
            }
        }

        if ($this->_maxScoreCache[$item] == $score) {
            return '<div style="color:coral">' . $score . '</div>';
        }
        return $score;
    }

    public function course()
    {
        if ($this->_meeting) {
            if ($user = $this->getService('User')->find($this->_participantId)->current()) {
                $this->view->setSubHeader($this->_meeting->title . ' - ' . $user->getName());
            }
        }
        
        if ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_meeting->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_meeting->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_meeting->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = array($itemId); 
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
                  
            }
        }

        $select = $this->getService('Meeting')->getSelect();
        $select->from(
                    array('l' => 'scorm_tracklog'),
                    array(
                        'l.trackID',
                        'MID' => 'mid',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        'item' => 'o.title',
                    	'parent' => 'o.oid',
                        'course_id' => 'l.cid',
                        'l.score',
                        'l.scoremax',
                        'l.scoremin',
                        'l.start',
                        'l.stop',
                        'l.status'
                    )
                )
                ->joinLeft(
                    array('p' => 'People'),
                    'p.MID = l.mid',
                    array()
                )->joinLeft(array('o' => 'organizations'), 'o.oid = l.ModID', array())
                ->where('l.cid = ?', $courseId);

        if (count($items)) {
            $select->where('l.ModID IN (?)', $items);
        }

        if ($this->_meeting) {
            $select->where('l.meeting_id = ?', $this->_meeting->getMeetingId());
        }

        if ($this->_participantId) {
            $select->where('l.mid = ?', $this->_participantId);
        }

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
             $select->order(array('item ASC', 'score DESC', 'start DESC'));
        }

        $grid = $this->getGrid($select,
            array(
                'trackID' => array('hidden' => true),
                'MID' => array('hidden' => true),
            	'course_id' => array('hidden' => true),
            	'parent' => array('title' => _('Раздел модуля')),
                'fio' => array('hidden' => true), //array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{MID}}') . '{{fio}}'),
                'item' => array('title' => _('Материал')),
                'score' => array('title' => _('Балл')),
                'scoremax' => array('title' => _('Мин/Mакс'), 'decorator' => '{{scoremin}}/{{scoremax}}'),
                'scoremin' => array('hidden' => true),
                'start' => array('title' => _('Начало сеанса')),
                'stop' => array('title' => _('Окончание  сеанса')),
                'status' => array('title' => _('Статус'))
            ),
            array(
                'fio' => null,
                'item' => null,
                'score' => null,
                'scoremax' => null,
                'start' => array('render' => 'DateTimeStamp'),
                'stop' => array('render' => 'DateTimeStamp'),
                'status' => array('values' => HM_Scorm_Track_Data_DataModel::getStatuses())
            )
        );

        $grid->updateColumn('start', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));
        $grid->updateColumn('stop', array('format' => array('dateTime', array('date_format' => Zend_Locale_Format::getDateTimeFormat()))));

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTrackStatusString'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->updateColumn('parent',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateParent'),
                    'params' => array('{{parent}}', '{{course_id}}')
                )
            )
        );

        $grid->updateColumn('score',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateScore'),
                    'params' => array('{{item}}', '{{score}}', $select)
                )
            )
        );


        // если студент просматривает свои результаты - скрываем поле с ФИО
        if ( $this->_participantId == $this->getService('User')->getCurrentUserId()
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
             //&& in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT))
        ) {
            $grid->updateColumn('fio', array('hidden' => true));
        } else {
            $this->view->allowBack = true;
            $this->view->projectId = $this->_meeting->CID;
            $this->view->meetingId = $this->_meeting->getMeetingId();
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function getTrackStatusString($status)
    {
        $status = HM_Scorm_Track_Data_DataModel::getStatus($status);
        return "<span class='nowrap'>{$status}</span>";
    }

    public function testMiniAction()
    {
        $this->view->setHeader(_('Протокол выполнения'));
        $c         = $_GET['c'] = 'mini';
        $stid      = $_GET['stid'] = $this->_getParam('stid', 0);
        $projectId = (int) $this->_getParam('project_id', 0);
        $meetingId  = $this->_getParam('meeting_id', 0);
        $meeting    = $this->getOne($this->getService('Meeting')->find($meetingId));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $log = $this->getService('TestResult')->getOne($this->getService('TestResult')->find($stid));
            if ( $log ) {
                if ($log->mid != $this->getService('User')->getCurrentUserId()) {
                    $this->_flashMessenger->addMessage(array('type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                                                             'message' => _('Вы можете просматривать результаты только своих тестов')));
                    $this->_redirector->gotoSimple('my','list','meeting',array('project_id' => $projectId));
                }
            }
        }

        if ($meeting) {
            $this->view->setSubHeader($meeting->title);
        }

        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }


        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();
        ob_start();

        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        include(APPLICATION_PATH.'/../public/unmanaged/test_log.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);

        $this->view->content = $content;
    }

    public function test()
    {

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'attempt_id_DESC');
        }
        $this->_request->setParam("masterOrdergrid", 'fio ASC');

        $select = $this->getService('QuestAttempt')->getSelect();
        $select->from(
            array('a' => 'quest_attempts'),
            array(
                'MID' => 'a.user_id',
                'attempt_id' => 'a.attempt_id',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'start_date' => 'a.date_begin',
                'bal' => 'a.score_sum',
                'percent' => new Zend_Db_Expr("CONCAT(ROUND(100 * a.score_weighted, 0), '%')"),
                'starttime' => 'a.date_begin',
                'fulltime' => 'a.date_end',
                'status' => 'a.status',
            )
        )
            ->joinLeft(
                array('p' => 'People'),
                'p.MID = a.user_id',
                array()
            )
            ->where('a.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT)
            ->where('a.context_event_id = ?', $this->_meeting->getMeetingId())
            ->where('p.MID > ?', 0);

        if ($this->_participantId) {
            $select->where('a.user_id = ?', $this->_participantId);
        }

        $locale = Zend_Locale::findLocale();

        /** @var $grid Bvb_Grid */
        $grid = $this->getGrid($select,
            array(
                'MID' => array('hidden' => true),
                'attempt_id' => array('hidden' => true),
                'starttime' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => '')) . '{{MID}}') . '{{fio}}'),
                'start_date' => array(
                    'title' => _('Дата попытки'),
                    'format' => array(
                        'DateTime',
                        array(
                            'date_format' => Zend_Locale_Format::getDateTimeFormat($locale)
                        )
                    ),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'quest', 'controller' => 'report', 'action' => 'attempt', 'attempt_id' => '')).'{{attempt_id}}">{{start_date}}</a>'
                ),
                'bal' => ($this->_meeting->getType() == HM_Event_EventModel::TYPE_POLL)?array('hidden' => true):array('title' => _('Балл')),
                'percent' => ($this->_meeting->getType() == HM_Event_EventModel::TYPE_POLL)?array('hidden' => true):array('title' => _('Процент')),
                'fulltime' => array('title' => _('Затрачено времени')),
                'status' => array('title' => _('Статус')),
            ),
            array(
                'fio' => null,
                'bal' => null,
                'percent' => null,
                //'questions' => null,
//                'start_date' => array('render' => 'DateTimeStamp'),
                'start_date' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976] //добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
                'fulltime' => null,
                'status' => array('values' => HM_Quest_Attempt_AttemptModel::getStatuses())
            )
        );

        $grid->updateColumn('fulltime',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'getDurationString'),
                        'params' => array('{{fulltime}}', '{{starttime}}')
                    )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'getStatusString'),
                        'params' => array('{{status}}')
                    )
            )
        );

        $grid->updateColumn('needmoder',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'getModerString'),
                        'params' => array('{{needmoder}}', '{{moder}}', '{{moderby}}', '{{modertime}}')
                    )
            )
        );

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER))
            $grid->addMassAction(array(
                'module' => 'meeting',
                'controller' => 'result',
                'action' => 'delete-attempt'
            ),
                _('Аннулировать попытки'),
                _('Вы уверены, что хотите аннулировать отмеченные попытки? При этом у соответствующих пользователей появятся дополнительные попытки для прохождения данного теста.')
            );

        // если студент просматривает свои результаты - скрываем поле с ФИО
        if ( $this->_participantId == $this->getService('User')->getCurrentUserId()
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //     && in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_participant))
        ) {

            $grid->updateColumn('fio', array('hidden' => true));
        }
        /*
                $grid->addAction(
                    array('module' => 'meeting', 'controller' => 'result', 'action' => 'test-mini'),
                    array('stid'),
                    $this->view->icon('print')
                );
        */
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function deleteAttemptAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $meetingId = (int) $this->_getParam('meeting_id', 0);

        $stids = $this->_request->getParam('postMassIds_grid');
        $stids = explode(',', $stids);

        if (count($stids)) {
            foreach($stids as $stid) {
                $result = $this->getOne($this->getService('TestResult')->find($stid));
                if ($result) {
                    // обновление попыток
                    $attempt = $this->getOne($this->getService('TestAttempt')->fetchAll(
                        $this->getService('TestAttempt')->quoteInto(
                            array('mid = ?', ' AND tid = ?', ' AND cid = ?', ' AND meeting_id = ?'),
                            array($result->mid, $result->tid, $projectId, $meetingId)
                        )
                    ));

                    if ($attempt) {
                        $attempt->qty--;
                        if ($attempt->qty < 0) $attempt->qty = 0;
                        $this->getService('TestAttempt')->update($attempt->getValues());
                    }
                }
                // удаление результатов
                $this->getService('TestResult')->delete($stid);
                $this->getService('QuestionResult')->deleteBy($this->getService('QuestionResult')->quoteInto('stid = ?', $stid));
            }
        }

        $this->_flashMessenger->addMessage(_('Попытки успешно удалены'));
        $this->_redirector->gotoSimple('index', 'result', 'meeting', array('project_id' => $projectId, 'meeting_id' => $meetingId));
    }

    public function task()
    {
        /**
         * Добавлено правильное отображение для Фио пользователя,
         * Добавлено отображение пользователей которым назначено задание, но нет результата.
         *
         * @author Artem Smirnov
         * @date 19.02.2013
         */
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT
        )
        {
            $this->test();
            return ;
        }

        $meetingId = (int) $this->_getParam('meeting_id', 0);

        // Полностью рефакторил этот фарш

        $subSelect = $this->getService('Interview')->getSelect()->from(
            array('i' => 'interview'),
            array(
                'real_user_id' => new Zend_Db_Expr('GREATEST(user_id, to_whom)'), // кто придумал такую структуру БД..?!!
                'real_interview_id' => 'MAX(interview_id)',
        ))
        ->where('meeting_id = ?', $meetingId)
        ->group('GREATEST(user_id, to_whom)');


        $select = $this->getService('MeetingAssign')->getSelect()->from(
            array('s' => 'meetingsID'),
            array(
                'user_id' => 'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'date' => 'i.date',
                'variant' => 'l.name',
                'i.type',
        ))->joinInner(array('p' => 'People'),
            's.MID = p.MID',
            array()
        )->joinInner(array('ss' => $subSelect),
            's.MID = ss.real_user_id',
            array()
        )->joinInner(array('i' => 'interview'), // последняя запись в interview
            'i.interview_id = ss.real_interview_id',
            array()
        )->joinInner(array('ii' => 'interview'), // первая запись в interview
            'ii.meeting_id = s.meeting_id',
            array()
        )->joinInner(array('l' => 'list'),
            'ii.question_id = l.kod',
            array()
        )
        ->where('meeting_id = ?', $meetingId)
        ->group('p.MID');

       $url = $this->view->url(array(
            'action' => 'card',
            'controller' => 'edit',
            'module' => 'user',
            'meeting_id' => $meetingId,
       ));

       $columns = array(
            'user_id' => array('hidden' => true),
            'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => ''), null, true) . '{{user_id}}') . "<a href='{$url}/user_id/{{user_id}}'>{{fio}}</a>"),
            'date' => array(
                'title' => _('Дата последнего изменения'),
                'callback' => array(
                    'function' => array($this, 'updateDate'),
                    'params' => array('{{date}}')
                )
            ),
            'type' => array(
                'title' => _('Текущий статус'),
                'callback' => array(
                    'function' => array($this, 'getTaskTypeString'),
                    'params' => array('{{type}}')
                )
            ),
            'variant' => array('title' => _('Название')),
       );

       $filters = array(
            'fio' => NULL,
            'date' => array('render' => 'date'),
            'type' => array('values' => HM_Interview_InterviewModel::getTypes())
       );

       $grid = $this->getGrid($select, $columns, $filters);

        $grid->addAction(
            array(
                'action' => 'index',
                'controller' => 'index',
                'module' => 'interview',
                'meeting_id' => $meetingId,
            ),
            array('user_id'),
            _('Просмотр')
        );

       $this->view->grid = $grid;

       return true;
       /*   следующий кусок кода попал сюда, как я думаю, за-за некорректного мержа.
        *   оставлю его в комменте, после return он все равно не работал.
\
            $this->_setParam('CID', $projectId);

            $s = Zend_Registry::get('session_namespace_unmanaged')->s;
            $params = $this->_getAllParams();
            if (is_array($params) && count($params)) {
                foreach($params as $key => $value) {
                    $$key = $value;
                }
            }
            $paths = get_include_path();
            set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
            $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
            $currentDir = getcwd();
            ob_start();

            chdir(APPLICATION_PATH.'/../public/unmanaged/');
            $res = include(APPLICATION_PATH.'/../public/unmanaged/test_moder.php');
            $content = ob_get_contents();
            ob_end_clean();
            set_include_path(implode(PATH_SEPARATOR, array($paths)));
            chdir($currentDir);

            if($res == 'update_ok'){
                $this->_flashMessenger->addMessage(_('Балл и комментарий успешно сохранены. Для перерасчета балла за задание выберите соответствующую опцию в списке "Выполнить действие" в нижней части страницы.'));
                $this->_redirector->gotoSimple('index', 'result', 'meeting', array('project_id' =>array('project_id' => $projectId ),'meeting_id' =>array('meeting_id' => (int) $this->_getParam('meeting_id', 0) )));
            }


            if($_POST['action'] == 'complete' || $_POST['action'] == 'clearsence'){
                $this->_flashMessenger->addMessage(_('Результаты успешно сохранены!'));
                $this->_redirector->gotoSimple('index', 'result', 'meeting', array('project_id' =>array('project_id' => $projectId ),'meeting_id' =>array('meeting_id' => (int) $this->_getParam('meeting_id', 0) )));
            }



            $this->view->content = $content;
        
        //*/
    }

    public function poll()
    {
    	//$quizId = $this->_meeting->getQuizId();
    	$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_meeting->getModuleId()));
		$quizId = $test->test_id;

        $select = $this->getService('Poll')->getSelect();
        $select->from(
                    array('qa' => 'quizzes_answers'),
                    array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title')
                )
                ->joinLeft(
                	array('qr' => 'quizzes_results'),
                	'qr.quiz_id=qa.quiz_id AND qr.question_id = qa.question_id AND qr.answer_id=qa.answer_id',
                	array('count' => 'COUNT(qr.user_id)')
                )
                ->where('qa.quiz_id = ?', $quizId)
                ->group(array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title'));
                //->order(array('qa.question_title', 'qa.answer_title')) ORDER не нужно использовать в селекте для грида
                ;

        $grid = $this->getGrid($select,
            array(
                'quiz_id' => array('hidden' => true),
                'question_id' => array('hidden' => true),
                'question_title' => array('title' => _('Текст вопроса')),
                'answer_title' => array('title' => _('Вариант ответа')),
                'answer_id' => array('hidden' => true),
                'count' => array('title' => _('Количество таких ответов')),
            ),
            array(
                'question_title' => null,
                'answer_title' => null,
            	'count' => null
            )
        );
        $grid->updateColumn('count',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'modifCount'),
                    'params' => array('{{count}}', '{{answer_title}}','{{question_id}}','{{quiz_id}}')
                )
            )
        );
		$grid->updateColumn('question_id',
					array('value' => '!!'));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
    public function modifCount($count,$answer_title,$question_id,$quiz_id){
//        $projectId = $this->_getParam('project_id', 0);
//        if ($answer_title == "свободный ответ")
//            return $count."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$this->view->lightDialogLink($this->view->baseUrl('test_vopros.php?kod='.$question_id.'&cid='.$projectId.'&mode=2&quiz_id='.$quiz_id.'&all=all',_('Карточка вопроса')), "Просмотр");
//        else
//            return $count;

        if ($answer_title == "свободный ответ" && $count>0){
            /*$select = $this->getService('Poll')->getSelect();
            $select->from(
                        array('qr' => 'quizzes_results'),
                        array('qr.freeanswer_data', 'qr.quiz_id', 'qr.question_id')
                    )
                    ->where('qr.quiz_id = ?', $quiz_id)
                    ->where('qr.question_id = ?', $question_id);

            $query = $select->query();
            $fetch = $query->fetchAll();
             * 
             */
            $where = $this->quoteInto(array('quiz_id = ?', ' AND question_id = ?'), array($quiz_id, $question_id));
            $results = $this->getService('PollResult')->fetchAll($where);
            $result = array('<p class="total">' . $results->count() . '</p>');
            foreach ($results as $value){
               $result[]='<p>' . $value->freeanswer_data . '</p>';
            }
            return implode('', $result);
        }
        else
            return $count;
    }
    public function pollLeader(){

    	//$quizId = $this->_meeting->getQuizId();
    	$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_meeting->getModuleId()));
		$quizId = $test->test_id;

        $select = $this->getService('Poll')->getSelect();
        $select->from(
                    array('qa' => 'quizzes_answers'),
                    array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title')
                )
                ->joinLeft(
                	array('qr' => 'quizzes_results'),
                	'qr.quiz_id=qa.quiz_id AND qr.question_id = qa.question_id AND qr.answer_id=qa.answer_id',
                	array('count' => 'COUNT(qr.user_id)')
                )
                ->where('qa.quiz_id = ?', $quizId)
                ->group(array('qa.quiz_id', 'qa.question_id', 'qa.question_title', 'qa.answer_id', 'qa.answer_title'))
                ->order(array('qa.question_title', 'qa.answer_title'))
                ;

        $grid = $this->getGrid($select,
            array(
                'quiz_id' => array('hidden' => true),
                'question_id' => array('hidden' => true),
                'question_title' => array('title' => _('Текст вопроса')),
                'answer_title' => array('title' => _('Вариант ответа')),
                'answer_id' => array('hidden' => true),
            	'count' => array('title' => _('Количество таких ответов'))
            ),
            array(
                'question_title' => null,
                'answer_title' => null,
            	'count' => null
            )
        );

		$grid->updateColumn('question_id',
					array('value' => '!!'));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    // данные проведения опроса для конкретного юзера (ссылка с названия опроса в сборе обратной связи)
    public function pollByUserAction(){

        $projectId = $this->_getParam('project_id', 0);
    	$userId = $this->_getParam('user_id', 0);
        $meetingId = $this->_getParam('meeting_id', 0);
    	//$test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($meeting->getModuleId()));
		//$quizId = $test->test_id;
        $meeting = $this->getOne($this->getService('Meeting')->findDependence('Moderator', $meetingId));
        if ($meeting) {
            $this->view->setSubHeader($meeting->title);
        }

        $claimant = $this->getOne($this->getService('Claimant')->fetchAllDependence(array('Moderator', 'Provider'), $this->getService('Claimant')->quoteInto('CID = ? AND MID = ?', $projectId, $userId)));
        if($claimant) {
        	$this->view->date = $claimant->begin;
        	$this->view->place = $claimant->place;
        	$this->view->provider = $claimant->provider;
        	$this->view->moderator = $meeting->moderator[0]->LastName.' '.$meeting->moderator[0]->FirstName.' '.$meeting->moderator[0]->Patronymic;
        }
        elseif($meeting){
        	$this->view->date = new Zend_Date($meeting->begin, Zend_Locale_Format::getDateFormat()); // $meeting->begin;
        	$this->view->place = _('дистанционно');
        	$this->view->provider = '—';
        	$this->view->moderator = $meeting->moderator[0]->LastName.' '.$meeting->moderator[0]->FirstName.' '.$meeting->moderator[0]->Patronymic;
        }

        $log = $this->getService('TestResult')->fetchAll($this->getService('TestResult')->quoteInto(array('meeting_id = ?', ' AND MID = ?'), array($meetingId, $userId)))->asArray();

       	$content = '';

        if(is_array($log) && count($log)){

	        $s = Zend_Registry::get('session_namespace_unmanaged')->s;
	        $params = $this->_getAllParams();
	        if (is_array($params) && count($params)) {
	            foreach($params as $key => $value) {
	                $$key = $value;
	            }
	        }
	        $c = $_GET['c'] = 'mini';
	        $paths = get_include_path();
	        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));
	        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
	        $currentDir = getcwd();

	        foreach ($log as $attempt){

				$stid = $attempt['stid'];
		        ob_start();
		        chdir(APPLICATION_PATH.'/../public/unmanaged/');
		        include(APPLICATION_PATH.'/../public/unmanaged/test_log.php');
		        $content .= ob_get_contents();
		        ob_end_clean();
		        set_include_path(implode(PATH_SEPARATOR, array($paths)));

			}

	        chdir($currentDir);


        }

        $this->view->content = $content;

    }


    public function dateChanger($date)
    {
        $dateObject = new Zend_Date($date);
        return $dateObject->getTimestamp();
    }

    public function updateDate($date)
    {
        $dateObject = new Zend_Date($date);
        return $dateObject->toString();
    }


    /**
     * Возвращает наименование типа по его числовому представлению
     * @param int $type
     * @return string
     */
    public function getTaskTypeString( $type )
    {
        $ivModel = HM_Interview_InterviewModel::factory(array('type' => intval($type)));
        return  $ivModel->getType();
    }


    /**
     * Возвращает ФИО студента с которым ведется диалог
     * @param int $hash - уникальный идентификатор диалога
     * @return string
     */
    public function getInterviewUserName($hash,$fio)
    {
        $interview = $this->getService('Interview')->getOne($this->getService('Interview')->fetchAll(array('interview_hash=?' => $hash, 'type=?'=>HM_Interview_InterviewModel::MESSAGE_TYPE_TASK)));
        $meetingId = (int) $this->_getParam('meeting_id', 0);
        if ( count($interview) ) {
            $user = $this->getService('User')->getOne($this->getService('User')->find($interview->to_whom));
            if ( $user ) {
                return $this->view->cardLink(
                    $this->view->url(
                        array(
                        	'action' => 'view',
						   'controller' => 'list',
						   'module' => 'user',
						   'user_id' => $user->MID
                        )
                    )
                    ) . '<a href="' . $this->view->url(
                            array(
                            	'action' => 'index',
						   		'controller' => 'index',
						   		'module' => 'interview',
						   		'user_id' => $user->MID,
                                'meeting_id' => $meetingId
                            )
                        ) . '">' . $user->getName() . '</a>';
            }
        }
        return _('Пользователь удален');
    }


    public function getDurationString($dateEnd, $dateStart) {    
        if($dateEnd && $dateStart) {
            $dateStart = new HM_Date($dateStart);
            $dateEnd   = new HM_Date($dateEnd);
            return HM_Date::getDurationString($dateEnd->get(Zend_Date::TIMESTAMP) - $dateStart->get(Zend_Date::TIMESTAMP));
        } else {
            return '';
        }
        
    }

    public function getStatusString($status)
    {
        $statuses = HM_Quest_Attempt_AttemptModel::getStatuses();
        return $statuses[$status];
    }
    public function getModerString($needmoder, $moder, $moderby, $modertime)
    {
        if ($needmoder == 0 && $moder == 0) return '&nbsp;';
        else {
            if ($needmoder == 1) return _("Сеанс еще не проверен преподавателем");
            else {
                $user = $this->getOne($this->getService('User')->find($moderby));
                if ($user) {
                    return sprintf('%s, %s', $user->getName(), date('d.m.Y H:i', $modertime)); // todo: HM_Date
                }
            }
        }
        return false;
    }

    public function updateParent($oid, $courseId){

        $separator = ' > ';

        if($this->_orgStructure == Null){
            $this->_orgStructure = $this->getService('CourseItem')->fetchAll(array('cid = ?' => $courseId));
        }

        $currItem = null;
        foreach($this->_orgStructure as $item){
            if($item->oid == $oid){
                $currItem = $item;
            }
        }

        if($currItem->level > 0){
            $currLevel = $currItem->level;
            $string = array();
            while($currItem->level > 0){
                foreach($this->_orgStructure as $item){
                    if($item->oid == $currItem->prev_ref){
                        $currItem = $item;
                        $string[] = $currItem->title;

                        if($currItem->prev_ref == -1){
                            continue 2;
                        }

                    }
                }
            }
            return implode($separator, $string);
        }else{
            return _('Нет');
        }
        return $currItem->title;
    }

    public function skillsoftAction()
    {
        $this->_meeting = $this->getOne($this->getService('Meeting')->find((int) $this->_getParam('meeting_id', 0)));
        $userId = $this->_getParam('user_id', $this->getService('User')->getCurrentUserId());

        if ($this->_meeting) {
            if ($user = $this->getService('User')->find($userId)->current()) {
                $this->view->setSubHeader($this->_meeting->title . ' - ' . $user->getName());
            }
        }

        if(
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //$this->getService('User')->getCurrentUserRole() != HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT
        ){
            $userId =  $this->_getParam('user_id', 0);
            $projectId = $this->_getParam('project_id', 0);
            $participants = $this->getService('Project')->getAssignedUsers($projectId);

            $resParticipants = array(0 => _('Выберите слушателя'));

            foreach($participants as $participant){
                $resParticipants[$participant->MID] = $participant->getName();
            }
            $this->view->participants = $resParticipants;

            if($userId == 0){
                return;
            }
        }
        $this->view->userId = $userId;
        $this->view->meetingId = $this->_meeting->meeting_id;

    }

    public function reportAction()
    {

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

		$reports = $this->getService('ScormReport')->fetchAll(array(
            'mid = ?' => $this->_getParam('user_id', 0),
            'lesson_id = ?' => $this->_getParam('meeting_id', 0),
            'subject = ?' => 'project'
        ));

        exit($reports[0]->report_data);
    }


}