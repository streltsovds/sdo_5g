<?php
class Subject_ResultsController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    private $_userId;
    private $_lesson;
    protected $_isEnduser;

    private $_maxScoreCache = null;

    public function init()
    {
        parent::init();

        $this->_isEnduser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER);
        $this->_userId = $this->_isEnduser ? $this->getService('User')->getCurrentUserId() : $this->_getParam('user_id', 0);

        if ($lessonId = $this->_getParam('lesson_id', 0)) {

            $backUrl = $this->view->url([
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => $this->_isEnduser ? 'index' : 'edit',
                'subject_id' => $this->_subjectId,
            ], null, true);

            if ($this->_lesson =  $this->getService('Lesson')->findOne($lessonId)) {

                $subSubHeader = _('Результаты занятия');
                if ($this->_userId && !$this->_isEnduser) {
                    /** @var HM_User_UserModel $user */
                    if ($user = $this->getService('User')->findOne($this->_userId)) {
                        $subSubHeader = sprintf(_('Результаты занятия - %s'), $user->getName());
                    }
                }

                $this->view->setSubSubHeader($subSubHeader);
                $this->view->setHeader($title = $this->_lesson->title);
                $this->view->setBackUrl($backUrl);

            } else {

                $this->_flashMessenger->addMessage([
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Занятие не найдено')
                ]);
                $this->_redirector->gotoUrl($backUrl);
            }
        }
    }

    public function indexAction()
    {
        if ($this->_lesson) {
            switch($this->_lesson->getType()) {
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_POLL:
                    $this->test();
                    break;
                case HM_Event_EventModel::TYPE_COURSE:
                case HM_Event_EventModel::TYPE_LECTURE: // ???
                    if(!$this->_getParam('userdetail',false)) {
                        $this->courseMain();
                    } else {
                        $this->course();
                    }
                    break;
                case HM_Event_EventModel::TYPE_TASK:
                    $this->task();
                    break;
                //case HM_Event_EventModel::TYPE_RESOURCE:
                default:
                    $this->common();
                    break;
            }
        }
    }

    // @todo
    public function scoAction()
    {

        $this->view->setSubSubHeader(_('Результаты работы с учебным модулем') . HM_View_Helper_Footnote::marker(1));

        if ($this->_lesson) {
            if ($user = $this->getService('User')->find($this->_userId)->current()) {
                $this->view->setSubHeader($this->_lesson->title . ' - ' . $user->getName());
            }
        }

        $items = array();
        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true);
            }
        }
                
        // вынес логику в ScormTrack
        list($itemResults, $fullProgress) = $this->getService('ScormTrack')->getAggregatedResults($courseId, $this->_lesson->getLessonId(), $this->_userId, $items);

        $this->view->items = $itemResults;
        $this->view->fullProgress = $fullProgress;
        $this->view->footnote(_('Отображается результат последней (хронологически) попытки'), 1);
    }

    public function courseMain()
    {
        $select = $this->getService('Lesson')->getSelect();

        //if ($this->_lesson->typeID == HM_Event_EventModel::TYPE_COURSE) {
        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
            $courseId = $courseId?$courseId:$this->_lesson->CID;//$this->_lesson->getModuleId(); //[che 5.06.2014] // В процессе решения #16976, оказалоь просто не поасть на страницу - заодно починил еее
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = array($itemId); 
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
                  
            }
        }

        $subSelect = $this->getService('Lesson')->getSelect();
        $subSelect->from( array('l' => 'scorm_tracklog'),
                          array('l.mid',
                                'l.cid',
                                'count' => new Zend_Db_Expr('COUNT(trackID)'),
                                'mscore' => new Zend_Db_Expr('MAX(score)')) )
                  ->where( 'l.cid = ?', $courseId)
                  ->group( array('l.mid','l.cid') );

        if ($items && count($items)) {
            $subSelect->where('l.ModID IN (?)', $items);
        }
        
        if ($this->_lesson) {
            $subSelect->where('l.lesson_id = ?', $this->_lesson->getLessonId());
        }

        if ($this->_userId) {
            $subSelect->where('l.mid = ?', $this->_userId);
        }

        $select->from( array('t1' => 'Students'),
                       array('t1.MID',
                             'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")) )
               ->joinLeft(array('p' => 'People'),
                           'p.MID = t1.MID',
                           array())
               ->joinLeft( array('t3' => $subSelect),
                           't1.MID = t3.mid',
                           array('t3.count', 't3.mscore'))
               ->joinLeft(array('les' => 'scheduleID'),
                          $this->getService('Lesson')->quoteInto('les.SHEID = ? AND les.MID = t1.MID',$this->_lesson->getLessonId()),
                          array('status' =>'les.V_DONE'))
               ->where('t1.CID = ?', $this->_getParam('subject_id', 0));

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
                    'function' => array($this, 'updateModuleStatus'),
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
    public function updateModuleStatus($status)
    {
        return HM_Lesson_Assign_AssignModel::getProgressStatusName($status);
    }

    /**
     *  Максимальный результат материала выделяется стилем
     * @param $item int
     * @param $score int
     * @param $select Zend_Db_Select
     */
    public function updateScore($item, $score, $select)
    {
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

        // @todo: использовать правильное выделение цветом (причём зеленым)
        if ($this->_maxScoreCache[$item] == $score) {
            return '<div style="color:coral">' . $score . '</div>';
        }
        return $score;
    }

    public function course()
    {
        $courseId = 0;
        $items    = [];

        if ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_COURSE)) {
            $courseId = $this->_lesson->getModuleId();
        } elseif ($this->getService('Event')->inheritsType($this->_lesson->typeID, HM_Event_EventModel::TYPE_LECTURE)) {
            if ($params = $this->_lesson->getParams()) {
                $courseId = $params['course_id'];     
                $itemId = $params['module_id'];  
                $items = [$itemId];
                if (count($collection = $this->getService('CourseItem')->getChildrenLevel($courseId, $itemId, false, true))) {
                    $items = $collection->getList('oid') + $items;
                }
            }
        }

        $select = $this->getService('Lesson')->getSelect();
        $select->from(['l' => 'scorm_tracklog'], [
            'l.trackID',
            'MID' => 'mid',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'item' => 'o.title',
            'parent' => 'o.oid',
            'course_id' => 'l.cid',
            'l.score',
            'l.scoremax',
            'l.scoremin',
            'start1'=>'l.start', //[che 5.06.2014 #16976]
            'l.stop',
            'l.status',
            'l.trackdata',
        ])
        ->joinLeft(['p' => 'People'], 'p.MID = l.mid', [])
        ->joinLeft(['o' => 'organizations'], 'o.oid = l.ModID', [])
        ->where('l.cid = ?', $courseId);

        if ($items && count($items)) {
            $select->where('l.ModID IN (?)', $items);
        }

        if ($this->_lesson) {
            $select->where('l.lesson_id = ?', $this->_lesson->getLessonId());
        }

        if ($this->_userId) {
            $select->where('l.mid = ?', $this->_userId);
        }

        if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
             $select->order(['item ASC', 'score DESC', 'start1 DESC']);
        }

        $columns = [
            'trackID'   => ['hidden' => true],
            'MID'       => ['hidden' => true],
            'course_id' => ['hidden' => true],
            'fio'       => ['hidden' => true],
            'scoremin'  => ['hidden' => true],
            'parent'    => ['title'  => _('Раздел модуля')],
            'item'      => ['title'  => _('Материал')],
            'score'     => ['title'  => _('Балл')],
            'scoremax'  => ['title'  => _('Мин/Mакс'), 'decorator' => '{{scoremin}}/{{scoremax}}'],
            'start1'    => ['title'  => _('Начало сеанса')],
            'stop'      => ['title'  => _('Окончание  сеанса')],
            'status'    => ['title'  => _('Статус')]
        ];

        $grid = $this->getGrid($select, $columns, [
            'fio'      => null,
            'item'     => null,
            'score'    => null,
            'scoremax' => null,
            'start1'   => ['render' => 'DateSmart'], // [che 5.06.2014 #16976
            'stop'     => ['render' => 'DateSmart'],  // добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
            'status'   => ['values' => HM_Scorm_Track_Data_DataModel::getStatuses()]
        ]);

        $grid->updateColumn('start1', [
            'format' => [
                'dateTime',
                ['date_format' => Zend_Locale_Format::getDateTimeFormat('ru_RU')]
        ]]);

        $grid->updateColumn('stop', [
            'format' => [
                'dateTime',
                ['date_format' => Zend_Locale_Format::getDateTimeFormat('ru_RU')]
        ]]);

        $grid->updateColumn('status', [
            'callback' => [
                'function' => [$this, 'updateTrackStatusString'],
                'params' => ['{{status}}', '{{trackdata}}']
            ]
        ]);

        $grid->updateColumn('parent', [
            'callback' => [
                'function' => [$this, 'updateParent'],
                'params' => ['{{parent}}', '{{course_id}}']
            ]
        ]);

        $grid->updateColumn('score', [
            'callback' => [
                'function' => [$this, 'updateScore'],
                'params' => ['{{item}}', '{{score}}', $select]
            ]
        ]);

        // если студент просматривает свои результаты - скрываем поле с ФИО
        if ($this->_isEnduser) {
            $grid->updateColumn('fio', ['hidden' => true]);
        } else {
            $this->view->allowBack = true;
            $this->view->subjectId = $this->_lesson->CID;
            $this->view->lessonId = $this->_lesson->getLessonId();
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateTrackStatusString($status, $trackdata)
    {
        $link = false;
        $status = HM_Scorm_Track_Data_DataModel::getStatus($status);

        if ($trackdata) {
        $data = unserialize($trackdata);
        if (is_array($data) && isset($data['cmi.suspend_data'])) {
            $jsonData = json_decode($data['cmi.suspend_data']);
            if ($jsonData && $jsonData->protocolLinks && is_array($jsonData->protocolLinks)) {
                $link = array_shift($jsonData->protocolLinks);
            }
        }
    }

    return $link 
        ? sprintf('<a href="%s" target="_blank" class="nowrap">%s</s>', $link, $status)
            : "<span class='nowrap'>{$status}</span>";
    }

    public function common()
    {
        $select = $this->getService('LessonLog')->getSelect();
        $select->from(
                   array('sl' => 'schedule_log'),
                   array(
                       'report_id' => 'sl.id',
                       'MID' => 'sl.user_id',
                       'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                       'date_start' => 'sl.date_start',
                   )
               )
               ->joinLeft(
                   array('p' => 'People'),
                   'p.MID = sl.user_id',
                   array()
               )
               ->where('sl.lesson_id = ?', $this->_lesson->getLessonId())
               ->where('p.MID > ?', 0);

        if ($this->_userId) {
           $select->where('sl.user_id = ?', $this->_userId);
        }

        $locale = Zend_Locale::findLocale();

        /** @var $grid Bvb_Grid */
        $grid = $this->getGrid($select,
           array(
               'MID' => array('hidden' => true),
               'report_id' => array('hidden' => true),
               'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array('action' => 'view', 'controller' => 'list', 'module' => 'user', 'user_id' => '')) . '{{MID}}') . '{{fio}}'),
               'date_start' => array(
                   'title' => _('Дата попытки'),
                   'format' => array(
                       'DateTime',
                       array(
                           'date_format' => Zend_Locale_Format::getDateTimeFormat($locale)
                       )
                   ),
               ),
           ),
           array(
               'fio' => null,
               'date_start' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976] //добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
           )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
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
                ->where('a.context_type = ?', HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING)
                ->where('a.context_event_id = ?', $this->_lesson->getLessonId())
                ->where('p.MID > ?', 0);

        if ($this->_userId) {
            $select->where('a.user_id = ?', $this->_userId);
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
                'bal' => ($this->_lesson->getType() == HM_Event_EventModel::TYPE_POLL)?array('hidden' => true):array('title' => _('Балл')),
                'percent' => ($this->_lesson->getType() == HM_Event_EventModel::TYPE_POLL)?array('hidden' => true):array('title' => _('Процент')),
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
                    'function' => array($this, 'updateDuration'),
                    'params' => array('{{fulltime}}', '{{starttime}}')
                )
            )
        );

        $grid->updateColumn('status',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            )
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'word-attempt',
            ), array(
                'attempt_id'
            ), _('Скачать отчёт')
        );

        $grid->addAction(array(
                'module' => 'quest',
                'controller' => 'report',
                'action' => 'attempt',
            ), array(
                'attempt_id'
            ), _('Отчёт online')
        );

        if (!$this->_isEnduser)
            $grid->addMassAction(array(
                'module' => 'subject',
                'controller' => 'results',
                'action' => 'delete-attempt'
            ),
                _('Аннулировать попытки'),
                _('Вы уверены, что хотите аннулировать отмеченные попытки? При этом у соответствующих пользователей появятся дополнительные попытки для прохождения данного теста.')
            );

         // если студент просматривает свои результаты - скрываем поле с ФИО
        if ($this->_isEnduser) {
            $grid->updateColumn('fio', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function deleteAttemptAction()
    {
        $attemptIds = $this->_request->getParam('postMassIds_grid');
        $attemptIds = explode(',', $attemptIds);

        if (count($attemptIds)) {
            foreach($attemptIds as $attemptId) {
                $this->getService('QuestQuestionResult')->deleteBy(array('attempt_id = ?' => $attemptId));
                $this->getService('QuestAttempt')->delete($attemptId);
            }
        }

        $this->_flashMessenger->addMessage(_('Попытки успешно удалены'));
        $this->_redirector->gotoSimple('index', 'results', 'subject', array('subject_id' => $this->_subjectId, 'lesson_id' => $this->_lesson->SHEID));
    }

    public function task()
    {
        $subSelect = $this->getService('TaskConversation')->getSelect()->from(
            array('tcc' => 'task_conversations'),
            array('lesson_id'=>'tcc.lesson_id', 
                  'user_id'=>'tcc.user_id', 
                  'date'=>new Zend_Db_Expr("MAX(tcc.date)"),
                  'variant_id'=>new Zend_Db_Expr("MAX(tcc.variant_id)")
                 )
            )
            ->where('lesson_id = ?', $this->_lesson->SHEID)
            ->group(array('tcc.lesson_id', 'tcc.user_id'));

        $select = $this->getService('LessonAssign')->getSelect()->from(
            array('s' => 'scheduleID'),
            array(
                'lesson_id' => 's.SHEID',
                'user_id' => 'p.MID',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'state' => 'tc.type',
                'date' => 'tc.date',
                'variant' => 'tv.name',
//                'i.type',
        ))->joinInner(array('p' => 'People'),
            's.MID = p.MID',
            array())
        ->joinLeft(array('tc1' => $subSelect),
            'tc1.lesson_id=s.SHEID and tc1.user_id=s.MID',
            array())
        ->joinLeft(array('tc' => 'task_conversations'),
            'tc.lesson_id=s.SHEID and tc.user_id=s.MID AND tc.date=tc1.date',
            array())
        ->joinLeft(array('tv' => 'tasks_variants'),
            'tv.variant_id=tc1.variant_id',
            array())
        ->where('SHEID = ?', $this->_lesson->SHEID)
        ->group(array('s.SSID', 's.SHEID', 'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'tc.variant_id', 'tc.type', 'tc.date', 'tv.name'))
        ->order(array('date DESC'));

       $columns = array(
            'user_id' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'fio' => array('title' => _('ФИО'), 'decorator' => "<a href='".$this->view->url(array('action' => 'index', 'controller' => 'conversation', 'module' => 'task'), null)."/user_id/{{user_id}}'>{{fio}}</a>"),
            'date' => array(
                'title' => _('Дата последнего изменения'),
                'callback' => array(
                    'function' => function($date) {
                        $hmDate = new HM_Date($date);
                        return $hmDate->get(HM_Date::DATETIME_MEDIUM);
                    },
                    'params' => array('{{date}}')
                )
            ),
            'state' => array(
                'title' => _('Текущий статус'),
                'callback' => array(
                    'function' => array($this, 'updateTaskTypeString'),
                    'params' => array('{{state}}')
                )

            ),
            'variant' => array('title' => _('Вариант')),
       );

       $filters = array(
           'fio' => null,
           'date' => array('render' => 'DateSmart'), // [che 5.06.2014 #16976] //добавил свой рендер фильтра, который интеллектуально обрабатывает пользовательский ввод и не допускает ошибок в SQL
           'state' => array('values' => HM_Task_Conversation_ConversationModel::getTypes()),
           'variant' => null
       );

       $grid = $this->getGrid($select, $columns, $filters);

       $this->view->grid = $grid;
    }

    public function poll()
    {
        $test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_lesson->getModuleId()));
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
                ->where('qr.lesson_id = ?', $this->_lesson->SHEID)
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
    public function modifCount($count,$answer_title,$question_id,$quiz_id)
    {
//        $subjectId = $this->_getParam('subject_id', 0);
//        if ($answer_title == "свободный ответ")
//            return $count."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$this->view->lightDialogLink($this->view->baseUrl('test_vopros.php?kod='.$question_id.'&cid='.$subjectId.'&mode=2&quiz_id='.$quiz_id.'&all=all',_('Карточка вопроса')), "Просмотр");
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
            $where = $this->quoteInto(array(
                'quiz_id = ?', ' AND question_id = ? AND ', 'lesson_id = ?'
            ), array(
                $quiz_id, $question_id, $this->_lesson->SHEID
            ));
            
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

    public function pollLeader()
    {
        $test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->find($this->_lesson->getModuleId()));
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
                ->where('qr.lesson_id = ?', $this->_lesson->SHEID)
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

    /**
     * Возвращает наименование типа по его числовому представлению
     * @param int $type
     * @return string
     */
    public function updateTaskTypeString( $state )
    {
        $types = HM_Task_Conversation_ConversationModel::getTypes();
        return  $types[$state];
    }


    public function updateDuration($dateEnd, $dateStart) 
    {    
        if($dateEnd && $dateStart) {
            $dateStart = new HM_Date($dateStart);
            $dateEnd   = new HM_Date($dateEnd);
            return HM_Date::getDurationString($dateEnd->get(Zend_Date::TIMESTAMP) - $dateStart->get(Zend_Date::TIMESTAMP));
        } else {
            return '';
        }
    }

    public function updateStatus($status)
    {
        $statuses = HM_Quest_Attempt_AttemptModel::getStatuses();
        return $statuses[$status];
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
}