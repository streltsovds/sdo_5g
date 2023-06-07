<?php

class Subject_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $classifierCache  = [];
    protected $sessionsCache    = [];
    protected $fromProgramArray = [0];
    protected $subjectId        = 0;
    protected $isLaborSafety    = 0;
    protected $subject          = null;
    protected $baseType         = false;
    protected $switcher         = '';

    public function init()
    {
        $this->setSubjectId();
        $this->setSubject();
        $this->setIsLaborSafety();
        $this->setBaseType();
        $this->setSwitcher();

        $form = new HM_Form_Subjects();
        $form->setDefault('cancelUrl', $this->view->url([
            'module'     => 'subject',
            'controller' => 'index',
            'action'     => 'card',
            'subject_id' => $this->subjectId
        ]));

        if ($this->subjectId) {
            $this->_subject = $this->getService('Subject')->fetchRow(['subid = ?' => $this->subjectId]);
            $form->getElement('icon')->setOptions(['subject' => $this->subject]);
        }
        else $form->setDefault('period', HM_Subject_SubjectModel::PERIOD_FREE);

        // Накидываем модификаторы формы
        $this->addModifier($form);
        $this->_setForm($form);

        parent::init();

        if ($this->_getParam('start',0) && $this->_getParam('end',0))
            $this->_helper->ContextSwitch()
                ->setAutoJsonSerialization(true)
                ->addActionContext('calendar', 'json')
                ->addActionContext('save-calendar', 'json')
                ->initContext('json');
    }

    public function indexAction()
    {
        if ($this->switcher && $this->switcher != 'index' && $this->switcher != 'list') {
            $this->getHelper('viewRenderer')->setNoRender();
            $action = $this->switcher.'Action';
            $this->$action();
            echo $this->view->render('list/'.$this->switcher.'.tpl');
            return true;
        }

        if (!$this->baseType) {
            if (!$this->isGridAjaxRequest() && $this->_request->getParam('ordergrid', '') == '') {
                $this->_request->setParam('ordergrid', 'name_ASC');
            }
        }

        $subjectService = $this->getService('Subject');
        $select = $subjectService->getSelect();
        $subSelect = clone $select;

        $subSelect->from(['subjects'], ['base_id' => 'subid', 'base_name'=> 'name']);

        $select->from(['s' => 'subjects'], [
            'subid' => 's.subid',
            'basetype' => 's.base',
            'name' => 's.name',
            'state'=> 's.state',
            'period_restriction_type' => 's.period_restriction_type',
            'base_name'  => 's2.base_name',
            'sessions'  => 's.base',
            'classifiers' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.name)'),
            'type' => 's.type',
            'begin' => "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.begin IS NULL) THEN s.begin ELSE s.begin END",
            'end' => "CASE WHEN (s.period_restriction_type = " . HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL . " AND s.end IS NULL) THEN s.end ELSE s.end END",
            'period' => 's.period',
            'longtime' => 's.longtime',
            'plan_users' => 's.plan_users'])
            ->joinLeft(['st' => 'Students'], 'st.CID = s.subid', [])
            ->joinLeft(['stp' => 'People' ], 'st.MID = stp.MID', ['students' => 'COUNT(DISTINCT stp.MID)'])
            // классификатор уч.курсов
            ->joinLeft(['cl' => 'classifiers_links'], 's.subid = cl.item_id AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, [])
            ->joinLeft(['c' => 'classifiers'], 'c.classifier_id = cl.classifier_id', [])
            ->joinLeft(['o' => 'offlines'], 'o.subject_id = s.subid', ['offline_id' => 'o.id', 'offline_date' => 'o.created'])
            ->joinLeft(['s2' => $subSelect], 's.base_id = s2.base_id', [])
            ->where("s.is_labor_safety = ?", $this->isLaborSafety)
            ->group([
                's.subid',
                's.base',
                's.name',
                's.state',
                's.period_restriction_type',
                's.begin',
                's.begin',
                's.end',
                's.end',
                's2.base_name',
                's.period',
                's.price',
                's.external_id',
                's.type',
                's.longtime',
                's.base_id',
                's.base',
                's.plan_users',
                'o.id',

                'o.created'
            ]
        );

        if ($this->currentUserRole([
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ])) {
            if ($this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                $select->where('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
                $select->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_DISTANCE);
            } else {
                $select->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_DISTANCE);
                $select->where('s.base != ? OR s.base IS NULL', HM_Subject_SubjectModel::BASETYPE_SESSION);
            }
        }

        //Область ответственности
        if ($this->currentUserRole([
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL ])) {
            $select = $this->getService('Responsibility')->checkSubjects($select, 's.subid');
        }

        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ENDUSER ])) {
            $select->joinInner( ['students' => 'Students'], 's.subid = students.CID', []);
            $select->where('students.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_TEACHER])) {
            $select->joinInner(['teachers' => 'Teachers'], 's.subid = teachers.CID', []);
            $select->where('teachers.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        $isDean = $acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]);
        $isTeacher = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_TEACHER);

//Внимание! $cardName влияет на отображение компонента vue
        if ($isTeacher || $isDean) {
            if ($this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION)
                 $cardName = _('Карточка учебной сессии');
            else $cardName = _('Карточка учебного курса');
        }   else $cardName = _('Карточка');

        $grid = $this->getGrid($select, array(
            'fixType' => array('hidden' => true),
            'state'    => array('hidden' => true),
            'period_restriction_type' => array('hidden' => true),
            'subid' => array('hidden' => true),
            'basetype'    => array('hidden' => true),
            'period' => array('hidden' => true),
            'longtime' => array('hidden' => true),
            'offline_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'decorator' => $this->view->cardLink(
                    $this->view->url(array('action' => 'card', 'subject_id' => '')) .
                    '{{subid}}', $cardName
                ) . ' <a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'lessons', 'action' => 'edit', 'subject_id' => '{{subid}}'), null, true, false) . '">{{name}}</a>'
            ),
            'begin' => array('title' => _('Дата начала'),
                'id' => 'dsad'
            ),
            'end' => array(
                'title' => _('Дата окончания')
            ),
            'students' => array(
                'title' => _('Количество слушателей')
            ),
            'plan_users' => array(
                'hidden' => ($this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION)?false:true,
                'title' => _('Запланированно слушателей')
            ),
            'type' => array('hidden' => true),
            'classifiers' => array(
                'title' => _('Классификация'),
                'callback' =>
                    array('function' => array($this, 'classifiersCache'),
                        'params'   => array('{{classifiers}}', $select)
                    ),
                'color' => HM_DataGrid_Column::colorize('classifiers')
            ),
            'base_name' => array(
                'title' => _('Учебный курс'),
                'hidden' => in_array($this->baseType, array(HM_Subject_SubjectModel::BASETYPE_BASE, HM_Subject_SubjectModel::BASETYPE_PRACTICE))
            ),
            'sessions' => array(
                'title' => _('Учебные сессии'),
                'hidden' => $this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION,
                'callback' =>
                    array('function' => array($this, 'sessionsCache'),
                        'params'   => array('{{subid}}', $select)
                    ),
                'color' => HM_DataGrid_Column::colorize('sessions')
            ),
        ),
            array(
                'name' => null,
                'base_name' => null,
                'students' => null,
                'classifiers' => null,
                'begin' => array('render' => 'DateSmart'),
                'end' => array('render' => 'DateSmart'),
                'type' => array('values' => HM_Subject_SubjectModel::getTypes())
            )
        );

        // Hide all unused fields for base subject
        if ($this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            //если фактическое количество студентов больше запланированного подсвечиваем строку красным
            $grid->setClassRowCondition("{{plan_users}} < {{students}} && {{plan_users}} > 0", 'highlighted');
        }

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('subid', 'subid' => 'subject_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('subid'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addAction(array(
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'copy'
        ),
            array('subid'),
            $this->view->svgIcon('copy', 'Копировать')
        );

        if ($this->baseType != HM_Subject_SubjectModel::BASETYPE_SESSION)
        $grid->addAction(array(
                'module'     => 'subject',
                'controller' => 'list',
                'action'     => 'new',
                'base'       => HM_Subject_SubjectModel::BASETYPE_SESSION
            ),
            array('subid'),
            $this->view->svgIcon('education', 'Создать учебную сессию')
        );

        $grid->addAction(array(
                'module'     => 'offline',
                'controller' => 'list',
                'action'     => 'new',
            ),
            array('subid'),
            $this->view->svgIcon('computer-monitor', 'Создать offline-версию')
        );

        $grid->addAction(array(
                'module'     => 'lesson',
                'controller' => 'import',
                'action'     => 'csv'
            ),
            array('subid'),
            _('Импортировать результаты из offline')
        );

        if ($isDean && $this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $grid->addAction([
                'module' => 'subject',
                'controller' => 'list',
                'action' => 'copy-from-base',
            ],
                ['subid'],
                _('Копировать содержимое из базового курса'),
                _('Вы действительно желаете удалить материалы и план занятий данной учебной сессии и скопировать их из базового курса?')
            );
        }

        // пункт "назначить тьюторов на курсы"
        $teacherCollection = $this->getService('User')->fetchAllJoinInner('Teacher');
        $teachers = array();

        if (count($teacherCollection)) {
            foreach ($teacherCollection as $teacher) {
                $teachers[$teacher->MID] = $teacher->getName();
            }
        }
        asort($teachers,SORT_LOCALE_STRING);
        $teachers = array(_('Выберите тьюторов')) + $teachers;

        $grid->addMassAction(array('module' => 'subject',
                                   'controller' => 'list',
                                   'action' => 'assign',
                                   'mode' => 'teacher'),
                             _('Назначить тьюторов'),
                             _('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));
        $grid->addSubMassActionSelect($this->view->url(array('module' => 'subject',
                                            'controller' => 'list',
                                            'action' => 'assign',
                                            'mode' => 'teacher')),
                                      'teachersId',
                                      $teachers);

        //пункт "удалить тьюторов с курсов"
        $grid->addMassAction(array('module' => 'subject',
                                   'controller' => 'list',
                                   'action' => 'assign',
                                   'mode' => 'noteacher'),
            _('Отменить назначение всех тьюторов'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );


        $grid->addMassAction(array(
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Вы действительно желате удалить выбранные курсы? При этом будет удалена вся статистика обучения по этим курсам. Продолжить?')
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
                'params' => array('{{end}}', '{{period}}', '{{longtime}}', '{{period_restriction_type}}')
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

        $grid->updateColumn('students',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateStudents'),
                    'params'=> array('{{students}}', '{{subid}}')
                )
            )
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{source}}','{{state}}', '{{period_restriction_type}}', '{{offline_id}}', '{{subid}}')
            )
        );

        if ($isDean) {
            $grid->addFixedRows(
                $this->_getParam('module'),
                $this->_getParam('controller'),
                $this->_getParam('action'),
                'subid'
            );

            $grid->updateColumn('fixType', array('hidden' => true));
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->baseType = $this->baseType;
        $this->view->grid     = $grid;
    }

    public function updateActions($source, $state, $type, $offlineId, $subjectId, $actions)
    {
        // похоже на частное требование заказчика, просочившееся в trunk
        if (($state != HM_Subject_SubjectModel::STATE_CLOSED) || ($type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)) {
            $this->unsetAction($actions, array('controller' => 'index', 'action' => 'statement'));
        }
        if (empty($offlineId)) {
            $this->unsetAction($actions, array('module' => 'lesson', 'controller' => 'import', 'action' => 'csv'));
        } else {
            $this->unsetAction($actions, array('module' => 'offline', 'controller' => 'list', 'action' => 'new'));
        }
        if ($this->baseType == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $this->unsetAction($actions, array('controller' => 'list', 'action' => 'new', 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION));
        } else {
            $this->unsetAction($actions, array('action' => 'copy-from-base'));
        }

        if (in_array($subjectId, HM_Subject_SubjectModel::getBuiltInCourses())) {
            $this->unsetAction($actions, array('module' => 'subject', 'controller' => 'list', 'action' => 'delete'));
        }

        return $actions;
    }


    public function calendarAction()
    {
        /** @var HM_User_UserService $userService */
        $userService    = $this->getService('User');
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');
        $view = $this->view;

        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            /** @var HM_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('Subject');
            /** @var HM_Holiday_HolidayService $holidayService */
            $holidayService = $this->getService('Holiday');

            $begin = $subjectService->getDateTime(intval($this->_getParam('start')));
            $end   = $subjectService->getDateTime(intval($this->_getParam('end')));

            $where = $subjectService->quoteInto(
                array(
                    'base=?',
                    ' AND  NOT ( begin >= ?',
                    ' AND end <= ?)',
                    ' AND is_labor_safety = ?',
                ),
                array(
                    HM_Subject_SubjectModel::BASETYPE_SESSION,
                    $end,
                    $begin,
                    $this->isLaborSafety
                )
            );

            $collection    = $subjectService->fetchAllManyToMany('User', 'Teacher', $where);
            $eventsSources = $subjectService->getCalendarSource($collection, '0000ff', true, $this->_getParam('user_id', null));

            // добавляются выходные и праздники
            // user_id попал сюда из PM..?
            //$where = $this->quoteInto(array('date >= ?',' AND date <= ?', ' AND user_id = ?'), array($begin, $end, 0));
            $where = $this->quoteInto(
                array(
                    'date >= ?',
                    ' AND date <= ?'
                ),
                array(
                    $begin,
                    $end
                )
            );

            $holidays = $holidayService->fetchAll($where);

            if ( count($holidays) ) {
                foreach ($holidays as $day) {
                    $eventsSources[] = array(
                        'id'    => $day->id,
                        'title' => $day->title,
                        'color' => "#c2c8d3",
                        'start' => $day->date,
                        'end'   => $day->date,
                        'editable' => false,
                        'borderColor' => '#ff0000'
                    );
                }
            }

            // добавляются произвольные мероприятия пользователей
            if (!$this->_getParam('no_user_events', false)) {
                if ($this->_getParam('user_id', null)) {
                    $where = $this->quoteInto(
                        array(
                            'date >= ?',
                            ' AND date <= ?',
                            ' AND user_id = ?'
                        ),
                        array(
                            $begin,
                            $end,
                            $this->_getParam('user_id', null)
                        )
                    );
                } else {
                    $where = $this->quoteInto(
                        array(
                            'date >= ?',
                            ' AND date <= ?',
                            ' AND user_id <> ?'
                        ),
                        array(
                            $begin,
                            $end,
                            0
                        )
                    );
                }

                $holidays = $holidayService->fetchAllDependence('User', $where);

                if ( count($holidays) ) {
                    foreach ($holidays as $day) {
                        $eventsSources[] = array(
                            'id'    => $day->id,
                            'title' => $day->title . ' ' . $day->users->current()->getName(),
                            'color' => "#c2c8d3",
                            'start' => $day->date,
                            'end'   => $day->date,
                            'editable' => false,
                            'borderColor' => '#00FF00'
                        );
                    }
                }
            }

            $view->assign($eventsSources);
        } else {
            $view->assign(array(
                'source' => array(
                    'module'=>'subject',
                    'controller'=>'list',
                    'action'=>'calendar',
                    'no_user_events' => 'y'
                ),
                'editable' => !$acl->inheritsRole($userService->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER),
            ));
        }
    }


    public function saveCalendarAction()
    {
        $subjectId = $this->_getParam('eventid',0);
        $begin     = $this->_getParam('start',0);
        $end       = $this->_getParam('end',0);

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $subjectId && $begin && $end) {

            $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
            if ($subject) {
                $data = array(
                    'subid' => $subject->subid,
                    'begin' => $this->getService('Subject')->getDateTime($begin/1000, true) . ' 00:00:00',
                    'end'   => $this->getService('Subject')->getDateTime($end/1000, true) . ' 23:59:59'
                );
                $res = $this->getService('Subject')->update($data);
                if ($res) {
                    $result = _('Данные успешно обновлены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }

    public function newAction()
    {
        $base =  $this->_getParam('base', 0);
        $subjectId =  $this->_getParam('subid', 0);

        if($base == HM_Subject_SubjectModel::BASETYPE_SESSION){

            $this->view->setSubHeader(_('Создание учебной сессии'));

            // часть атрибутов не нужно наследовать от basesubject, поэтому true
            $this->setDefaults($this->_getForm(), true);
            $regType = $this->_getForm()->getElement('reg_type');
            $regType->setValue(HM_Subject_SubjectModel::REGTYPE_ASSIGN_ONLY);

            $baseId = $this->_getForm()->getElement('base_id');
            $baseId->setValue($subjectId);

        } else {
            $this->view->setSubHeader(_('Создание учебного курса'));
        }

        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getParams();
            // @todo: убрать этот хак после решения #31891
            if (empty($post['scale_id'])) $post['scale_id'] = HM_Scale_ScaleModel::TYPE_CONTINUOUS;

            if ($form->isValid($post)) {
                $result = $this->create($form);
                if ($result != NULL && $result !== TRUE) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    if ($this->subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                        $this->_redirector->gotoSimple('description', 'index', 'subject', ['subject_id' => $this->subject->subid]);
                    }
                    $this->_redirectToIndex();
                } else {
                    // Создаем форум курса
                    $this->getService('Forum')->getForumBySubject($this->subject);

                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    if ($this->subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                        $this->_redirector->gotoSimple('description', 'index', 'subject', ['subject_id' => $this->subject->subid]);
                    }
                    $this->_redirectToIndex();
                }
            }
        }
        $this->view->form = $form;
    }



    public function disperseAction(){

        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_getParam('subject_id', 0);
        $svitcher = $this->_getParam('switcher', 'list');


        if($subjectId > 0){
            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if($subject){

                $this->getService('Subject')->unassignStudent($subjectId, $userId);
                $this->_flashMessenger->addMessage(sprintf(_('Вы отчислены с курса %s'), $subject->name));

                // Проверяем на присутствие курсов у слушателя, если нет, то редиректим на главную пользователя
                $subjects = $this->getService('Student')->getSubjects($userId);
                if (!count($subjects)) {
                    $this->getService('User')->switchRole(HM_Role_Abstract_RoleModel::ROLE_USER);
                    $this->session = new Zend_Session_Namespace('default');
                    $this->session->switch_role = 1;

                    $this->_redirector->gotoSimple('description', 'index', 'default');
                }

            }else{
                $this->_flashMessenger->addMessage(_('Курс не найден'));
            }

        }else{
            $this->_flashMessenger->addMessage(_('Курс не найден'));
        }
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher'=>$svitcher));
    }

    public function unlookAction(){

        $userId = $this->getService('User')->getCurrentUserId();
        $subjectId = $this->_getParam('subject_id', 0);


        if($subjectId > 0){
            $subject = $this->getOne($this->getService('Subject')->find($subjectId));
            if($subject){

                $this->getService('Graduated')->updateWhere(array('is_lookable' => HM_Role_GraduatedModel::UNLOOKABLE), array('MID = ?' => $userId, 'CID = ?' => $subjectId));

                $this->_flashMessenger->addMessage(_('Курс удален из списка'));

            }else{
                $this->_flashMessenger->addMessage(_('Курс не найден'));
            }

        }else{
            $this->_flashMessenger->addMessage(_('Курс не найден'));
        }

        $this->_redirector->gotoSimple('index', 'list', 'subject');

    }

    /**
     * Экшен приаттачивания, слушателей, преподователей к курсам
     */
    public function assignAction()
    {
        $mode = $this->_getParam('mode',false);
        switch ( $mode) {

            case 'users': //приаттачиваем пользователей к курсам
                          $users = $this->_getParam('usersId',array());
                          $users = explode(',', $users[0][0]);
                          $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));

                          $this->getService('Lesson')->beginProctoringTransaction();

                          if ( $this->usersAssign($subjects,$users) ) {
                              $this->_flashMessenger->addMessage(_('Слушатели успешно назначены'));
                          } else {
                              $this->_flashMessenger->addMessage(_('При назначении некоторых слушателей возникли ошибки'));
                          }

                          $this->getService('Lesson')->commitProctoringTransaction();
                          break;
            case 'teacher': // приаттачиваем тьюторов к курсам
                            $teachers = $this->_getParam('teachersId',array());
                            $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
                           if ( $this->teachersAssign($subjects,$teachers) ) {
                              $this->_flashMessenger->addMessage(_('Тьюторы успешно назначены'));
                           } else {
                              $this->_flashMessenger->addMessage(_('При назначении некоторых тьюторов возникли ошибки'));
                           }
                          break;
            case 'noteacher': // убираем всех тьюторов курсов
                            $subjects = explode(',',$this->_getParam('postMassIds_grid',array()));
                           if ( $this->teachersDiscard($subjects) ) {
                              $this->_flashMessenger->addMessage(_('Назначение тьюторов успешно отменено'));
                           } else {
                              $this->_flashMessenger->addMessage(_('При отмене назначения некоторых тьюторов возникли ошибки'));
                           }
                          break;
            default:
                    $this->_flashMessenger->addMessage(_('Выбрано некорректное действие'));
                    break;
        }
        $this->_redirectToIndex();
    }


    /**
     * Удаляем всх тьюторов для выбраных курсов
     * @param int|array $subjects
     * @return boolean
     */
    private function teachersDiscard($subjects)
    {
        if ( !$subjects ) return false;

        $subjects = (array) $subjects;
        // приводим элемнты переданного массива к int
        array_walk($subjects,create_function('&$val,$key', '$val = (int) $val;'));
        $where = implode(',', $subjects);

        return $this->getService('Teacher')->deleteBy("CID IN ({$where})");
    }

    /**
     * приаттачиваем пользователей к курсам
     * @param int|array $subjects курсы
     * @param int|array $users пользователи
     * @return boolean
     * @todo Эту и подобную функции прорефакторить
     */
    private function usersAssign($subjects, $users)
    {
        if ( !$subjects || !$users ) return false;

        $result = true;
        $subjects = (array) $subjects;
        $users    = (array) $users;

        $subjectService = $this->getService('Subject');
        $userService = $this->getService('User');

        foreach ( $subjects as $subject ) {
         // проверка существования курса
         if ( !count($subjectService->find($subject))) {
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
            if ( !$subjectService->isStudent($subject,$user) ) {
                $subjectService->assignUser($subject,$user);
            }
         }
        }
        return $result;
    }

    /**
     * Приаттачивание тьюторов к курсам
     * @param int|array $subjects
     * @param int|array $teachers
     * @return boolean
     */
    private function teachersAssign($subjects, $teachers)
    {
        if ( !$subjects || !$teachers ) return false;
        $result = true;
        $subjects = (array) $subjects;
        $teachers = (array) $teachers;

        $teacherService = $this->getService('Teacher');
        $subjectService = $this->getService('Subject');

        foreach ( $subjects as $subject ) {
           // проверка существования курса
           if ( !count($subjectService->find($subject))) {
              $result = false;
              continue;
           }
           foreach ( $teachers as $teacher ) {
              // проверка существования тьютора
              if ( $teacherService->isUserExists($subject,$teacher) ) {
                  $result = false;
                  continue;
              }
              // если все ОК - создаем препода
              $teacherService->insert(array('MID'=>$teacher,'CID' => $subject));
           }
        }

        return $result;
    }

    public function programmAction()
    {
        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER) ) {
            $user = $this->getService('User')->getCurrentUser();
            $this->view->programms = $this->getService('Programm')->getUserProgramms($user->MID, HM_Programm_ProgrammModel::TYPE_ELEARNING);
            $this->view->user = $user;

            $now = date('Y-m-d H:i:s');

            $where = $this->quoteInto(
                array(
                    'self.MID = ? AND ',
                    '((Subject.period = ?) OR ',
                    '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                    '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                    '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                    '(Subject.period = ?',' AND self.end_personal > ?))',
                ),
                array(
                    $this->getService('User')->getCurrentUserId(),
                    HM_Subject_SubjectModel::PERIOD_FREE,
                    $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                    $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                    HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                    HM_Subject_SubjectModel::PERIOD_FIXED, $now,
                )
            );


            $students = $this->getService('Student')->fetchAllDependenceJoinInner('Subject', $where);

            $studentCourseData = array();
            foreach ($students as $student) {
                $studentCourseData[$student->CID] = array(
                    'begin' => $student->time_registered,
                    'end' => $student->end_personal,
                );
            }

            $marks = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $this->getService('User')->getCurrentUserId()))->getList('cid', 'mark');
            foreach ($studentCourseData as $subjectId => $data) {
                if (!isset($marks[$subjectId])) $marks[$subjectId] = HM_Scale_Value_ValueModel::VALUE_NA;
            }
            $this->view->marks = $marks;

            $this->view->studentCourseData = $studentCourseData;
            $this->view->graduatedList = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));

            $currentUserId = $this->getService('User')->getCurrentUserId();
            $userPrograms = $this->getService('Programm')->getUserProgramms($currentUserId);
            $userProgramIds = array();
            foreach ($userPrograms as $userProgram) {
                $userProgramIds[] = $userProgram['programm_id'];
            }
            if (empty($this->fromProgramArray) || $this->fromProgramArray[0] == 0) {
                $programEventUsers = $this->getService('ProgrammEventUser')->fetchAllDependence(
                    'ProgrammEvent',
                    $this->getService('ProgrammEventUser')->quoteInto(
                        array(
                            ' user_id = ? ',
                            ' AND programm_id IN (?) '
                        ),
                        array(
                            $currentUserId,
                            empty($userProgramIds) ? array(0) : $userProgramIds
                        )
                    )
                );

                foreach ($programEventUsers as $programEventUser) {
                    if ($programEventUser->programmEvent && $programEventUser->programmEvent->current()->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                        $programEventId = $programEventUser->programmEvent->current()->programm_event_id;
                        $programEvent = $this->getService('ProgrammEvent')->find($programEventId)->current();
                        $this->fromProgramArray[] = $programEvent->item_id;
                    }
                }
            }

            $this->view->fromProgramArray = $this->fromProgramArray;

        }
    }

	/*
	 *  Почти DEPRECATED!
	 *  Для слушателя есть Subject_MyController
	 *  Для препода - рефакторить
	 */
    public function listAction()
    {
        if ($this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        ) {
            $this->_redirector->gotoSimple('index', 'my', 'subject');
        } else {
            $this->_redirector->gotoSimple('index', 'list', 'subject', ['base' => HM_Subject_SubjectModel::BASETYPE_PRACTICE]);
        }

        $now = date('Y-m-d H:i:s');
        $listSwitcher = $this->_getParam('list-switcher', 'current');

        if (!isset($this->view->disabledSwitcherMods)) $this->view->disabledSwitcherMods = array();

        $subjects = null;
        $where = '';
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $userPrograms = $this->getService('Programm')->getUserProgramms($currentUserId);
        $userProgramIds = array();
        foreach ($userPrograms as $userProgram) {
            $userProgramIds[] = $userProgram['programm_id'];
        }

        if (empty($this->fromProgramArray) || $this->fromProgramArray[0] == 0 ) {
            $programEventUsers = $this->getService('ProgrammEventUser')->fetchAllDependence(
                'ProgrammEvent',
                $this->getService('ProgrammEventUser')->quoteInto(
                    array(
                        ' user_id = ? ',
                        ' AND programm_id IN (?) '
                    ),
                    array(
                        $currentUserId,
                        empty($userProgramIds) ? array(0) : $userProgramIds
                    )
                )
            );

            foreach ($programEventUsers as $programEventUser) {
                if ($programEventUser->programmEvent && $programEventUser->programmEvent->current()->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $programEventId = $programEventUser->programmEvent->current()->programm_event_id;
                    $programEvent = $this->getService('ProgrammEvent')->find($programEventId)->current();
                    $this->fromProgramArray[] = $programEvent->item_id;
                }
            }
        }

//        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
	if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))){
//[22.05.2014 #16913]

/*
Состояние полей для вариантов назначений:
                                    period  period_restriction_type
1. Начало и конец упр. препод.          0       2
2. Нестрогое                            0       1
3. Строгое                              0       0
4. Без ограничений                      1       0
5. С фикс. продолжительностью           2       0

*/

            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            '(self.MID = ? AND ',
                            '((self.time_registered >= ?', ' AND Subject.period = ? ) OR',
                            '(Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type IN (?)) OR ',
                            '(self.time_registered >= ?',' AND Subject.period = ?',' AND Subject.period_restriction_type IN (?)) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))', ' AND Subject.subid NOT IN (?)) OR ',
                            '(self.MID = ? ',' AND self.time_registered >= ?', ' AND Subject.subid IN (?))',
                        ),
                        array(
                            $currentUserId,
                            $now, HM_Subject_SubjectModel::PERIOD_FREE,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, array(HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL),
                            $now, HM_Subject_SubjectModel::PERIOD_FIXED, array(HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL),
                            HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL, $this->fromProgramArray,
                            $currentUserId, $now, $this->fromProgramArray,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            '(self.MID = ? AND ',
                            '((self.time_registered < ?', ' AND Subject.period = ? ) OR',
                            '(Subject.period = ?', ' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(self.time_registered < ?', ' AND Subject.period = ?',' AND self.end_personal > ?))', ' AND Subject.subid NOT IN (?)) OR ',
                            '(self.MID = ? ',' AND self.time_registered <= ? AND self.end_personal >= ?', ' AND Subject.subid IN (?))',
                        ),
                        array(
                            $currentUserId,
                            $now, HM_Subject_SubjectModel::PERIOD_FREE,
                            HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            $now, HM_Subject_SubjectModel::PERIOD_FIXED, $now, $this->fromProgramArray,
                            $currentUserId, $now, $this->fromProgramArray,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            '(self.MID = ? AND ',
                            '((Subject.end < ?', ' AND Subject.period = ?', ' AND Subject.period_restriction_type = ?', ' AND Subject.subid NOT IN (?)) OR ',
                            '(Subject.state = ?',  ' AND Subject.period = ?', ' AND Subject.period_restriction_type = ?', ' AND Subject.subid NOT IN (?)) OR ',
                            '(Subject.period = ?', ' AND self.end_personal < ?', ' AND Subject.subid NOT IN (?)))) OR ',
                            '(self.MID = ? ',' AND self.time_registered < ? AND self.end_personal < ?', ' AND Subject.subid IN (?))',
                        ),
                        array(
                            $currentUserId,
                            $now,HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT, $this->fromProgramArray,
                            HM_Subject_SubjectModel::STATE_CLOSED, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL, $this->fromProgramArray,
                            HM_Subject_SubjectModel::PERIOD_FIXED, $now, $this->fromProgramArray,
                            $currentUserId, $now, $this->fromProgramArray,
                        )
                    );
                    break;
            }

            $studentCourseData = array();
            $students = $this->getService('Student')->fetchAllDependenceJoinInner('Subject', $where);
            $courses = $students->getList('SID', 'CID');

            $userId = $this->getService('User')->getCurrentUserId();
            $selfClaimants = array();
            if (count($courses)) {
                $selfClaimants = $this->getService('Claimant')->fetchAll($this->quoteInto(
                    array('MID = ? ', 'AND CID IN (?) ', ' AND status = ?', ' AND created_by = ?'),
                    array($userId, $courses, HM_Role_ClaimantModel::STATUS_ACCEPTED, $userId)
                ))->getList('CID', 'CID');
            }

            if ($listSwitcher == 'past') {
                $graduated = $this->getService('Graduated')->fetchAll(
                    array(
                        'MID = ?' => $currentUserId,
                        'is_lookable = ?' => HM_Role_GraduatedModel::LOOKABLE,
                        'CID NOT IN (?)' => $this->fromProgramArray
                    )
                );
                $graduatedCourses = $graduated->getList('SID', 'CID');
                $courses = array_merge($courses, $graduatedCourses);

                foreach ($graduated as $grad) {
                    $studentCourseData[$grad->CID] = array(
                        'begin' => $grad->begin,
                        'end' => $grad->end,
                    );
                }
            }

            foreach ($students as $student) {
                $studentCourseData[$student->CID] = array(
                    'begin' => $student->time_registered,
                    'end' => $student->end_personal,
                );
            }

            $marks    = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $currentUserId))->getList('cid', 'mark');
            foreach ($studentCourseData as $subjectId => $data) {
                if (!isset($marks[$subjectId])) $marks[$subjectId] = HM_Scale_Value_ValueModel::VALUE_NA;
            }
            $this->view->marks = $marks;

            if (count($courses)) {
                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ')', 'name');
            }
            $this->view->share = true; // allow facebook etc.

            $feedback = $this->getService('Feedback')->getUserFeedback($currentUserId);
            foreach ($studentCourseData as $subjectId => $data) {
                if (isset($feedback[$subjectId])) $studentCourseData[$subjectId]['feedback'] = $feedback[$subjectId]['feedbacks'];
            }

            $this->view->studentCourseData = $studentCourseData;
            $this->view->graduatedList = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));

             /*------Выбор из таблицы SCORM_TRACKLOG------------------------*/

            // $userId = $this->getService('User')->getCurrentUserId();
            // $serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');
            // $sec = Array();
            // $out = explode( ',',$in);
            // /* Получение списка модулей по курсам */
            // for ($i=0;$i<count($out);$i++){
                // $selectLess = $serviceSubject->getSelect();
                // $selectLess->from(array('l' => 'lessons'),
                    // array(
                        // 'CID' => 'l.cid',
                        // 'SHEID' => 'l.sheid',
                        // 'title' => 'l.title',
                        // )
                    // )->where('l.cid = ? ', $out[$i]);

                        // if ($rowsetLess = $selectLess->query()->fetchAll()) {
                            // foreach ($rowsetLess as $rowLess) {

                            // /*Подсчет времени по каждому модулю*/

                                // $select = $serviceSubject->getSelect();
                                // $select->from(array('s' => 'scorm_tracklog'),
                                // array(
                                    // 'mid' => 's.mid',
                                    // 'lesson_id' => 's.lesson_id',
                                    // 'timer' => 'SUM(UNIX_TIMESTAMP(STOP ) - UNIX_TIMESTAMP(START ))',
                                    // )
                                // )->where('mid ='. $userId .' AND lesson_id ='. $rowLess['SHEID']);

                                    // if ($rowset = $select->query()->fetchAll()) {
                                        // foreach ($rowset as $row) {

                                        // /* Формирование массива для вывода данных*/

                                        // if(array_key_exists($out[$i], $sec)){
                                            // $sec[$out[$i]]=$sec[$out[$i]]+$row['timer'];
                                            // } else {
                                            // $sec[$out[$i]]=$row['timer'];
                                            // }
                                        // }
                                    // }
                            // }
                        // }

                 // $this->view->sec = $sec;

        // }

        // /* Log User */
        // $logs = array();
        // foreach ($students as $student) {
            // $selectTry = $serviceSubject->getSelect();
            // $selectTry->from(array('u' => 'loguser'),
            // array(
                // 'CID' => 'u.cid',
                // 'MID' => 'u.mid',
                // )
            // )->where('CID = '.$student->CID.' AND MID = '.$student->MID); // Количество попыток пользователя в пределах курса
                // if ($rowLog = $selectTry->query()->fetchAll()) {
                    // if(array_key_exists($student->CID, $logs)){

                    // /* Формирование массива для вывода данных*/

                        // $logs[$student->CID]=$logs[$student->CID]+count($rowLog);
                    // } else {
                        // $logs[$student->CID]=count($rowLog);
                    // }
                    // }
                // }
        // $this->view->logs = $logs;

        // /*Forums Messages*/
        // $my_mess = array();
        // foreach ($students as $student) {
            // $selectList = $serviceSubject->getSelect();
            // $selectList->from(array('m' => 'forums_list'),
            // array(
                // 'subject_id' => 'm.subject_id',
                // 'forum_id' => 'm.forum_id',
                // )
            // )->where('subject_id = '.$student->CID);  // Получение списка форумов в пределах курса.
                // if ($rowList = $selectList->query()->fetchAll()) {
                        // foreach ($rowList as $rList) {
                            // $selectMess = $serviceSubject->getSelect();
                            // $selectMess->from(array('mes' => 'forums_messages'),
                            // array(
                                // 'user_id' => 'mes.user_id',
                                // 'forum_id' => 'mes.forum_id',
                                // )
                            // )->where('user_id = '.$student->MID. ' AND forum_id = '.$rList['forum_id']);

                         // /*Подсчет количества сообщений в пределах курса*/

                            // if ($rowMess = $selectMess->query()->fetchAll()) {
                                // if(array_key_exists($student->CID, $my_mess)){
                                    // $my_mess[$student->CID]=$my_mess[$student->CID]+count($rowMess);
                                // } else {
                                    // $my_mess[$student->CID]=count($rowMess);

                                // }
                            // }
                        // }
                // }
        // }
        // $this->view->my_mess = $my_mess;

    /*********------------------------------*/
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {

            switch ($listSwitcher) {
                case 'future':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::STATE_PENDING, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        )
                    );
                    break;
                case 'current':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.period = ?) OR ',
                            '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.begin < ?',' AND Subject.end > ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR ',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(Subject.period = ?))',
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            HM_Subject_SubjectModel::PERIOD_FREE,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Subject_SubjectModel::PERIOD_FIXED,
                        )
                    );
                    break;
                case 'past':
                    $where = $this->quoteInto(
                        array(
                            'self.MID = ? AND ',
                            '((Subject.end < ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(Subject.end < ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?) OR',
                            '(Subject.state = ?',' AND Subject.period = ?',' AND Subject.period_restriction_type = ?))',
                        ),
                array(
                            $this->getService('User')->getCurrentUserId(),
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                            HM_Subject_SubjectModel::STATE_CLOSED, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                )
            );
                    break;
            }

            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
            $teachers = $this->getService('Teacher')->fetchAllDependenceJoinInner('Subject', $where);
            $courses = $teachers->getList('PID', 'CID');

            if (count($courses)) {
                $where = $this->getService('Subject')->quoteInto(
                    array('subid IN (?)'),
                    array(
                        $courses,
                    )
                );
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' , $where, 'name');
            }
            $this->view->share = false; // allow facebook etc.
                }

        } else {
            if(count($courses)){
            $this->view->is_student = true;

                $userId = $this->getService('User')->getCurrentUserId();
                $serviceClaimant = $this->getService('Claimant');
                $selectClaimants = $serviceClaimant->getSelect();
                $selectClaimants->from(array('c' => 'claimants'),
                    array(
                        'subid' => 'c.CID',
                    )
                )->where($this->quoteInto(array('CID IN (?)',' AND MID = ?', ' AND status = ?'), array(array_values($courses), $userId, HM_Role_ClaimantModel::STATUS_ACCEPTED)));
                $claimantSubjects = $selectClaimants->query()->fetchAll();

                $claimantSubjectSIDs = array();
                if(count($claimantSubjects)){
                    foreach ($claimantSubjects as $claimantSubject)
                    {
                        $subId = $claimantSubject['subid'];
                        $claimantSubjectSIDs[$subId] = $subId;
                    }
                }
            }
        }

        if($subjects){
            foreach ($subjects as $subject) {
                $subject->isUnsubscribleSubject = isset($claimantSubjectSIDs[$subject->subid]);
            }
        }

/*        switch ($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_Abstract_RoleModel::ROLE_STUDENT:
                $students = $this->getService('Student')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));
                $courses = $students->getList('SID', 'CID');

                $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId(), 'is_lookable = ?' => HM_Role_GraduatedModel::LOOKABLE));

                $courses = array_merge($courses, $graduated->getList('SID', 'CID'));
                $marks = $this->getService('SubjectMark')->fetchAll(array('MID =?' => $this->getService('User')->getCurrentUserId()));
                $this->view->marks = $marks->getList('cid', 'mark');

                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ')', 'name');
                $this->view->share = true; // allow facebook etc.


                $this->view->graduatedList = $this->getService('Graduated')->fetchAll(array('MID = ?' => $this->getService('User')->getCurrentUserId()));
                $this->view->studentCourseData = $studentCourseData;

                break;
            case HM_Role_Abstract_RoleModel::ROLE_TEACHER:
                $teachers = $this->getService('Teacher')->fetchAll(
                    array(
                        'MID = ?' => $this->getService('User')->getCurrentUserId()
                    )
                );
                $courses = $teachers->getList('PID', 'CID');

                $in = implode(',', $courses);
                $subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher' ,'subid IN (' . $in . ') AND end > ' . $this->getService('Subject')->getSelect()->getAdapter()->quote(date('Y-m-d', strtotime('-1 day'))), 'name');
                $this->view->share = false; // allow facebook etc.

                break;

        }*/

        $this->view->programms = $userPrograms;
        $this->view->listSwitcher = $listSwitcher;
        $this->view->subjects = $subjects;
        $this->view->fromProgramArray = $this->fromProgramArray;
    }



    protected function _getMessages() {


        if($this->_form->hasModifier('HM_Form_Modifier_BaseTypeSession')){
            return array(
                self::ACTION_INSERT    => _('Учебная сессия успешно создана'),
                self::ACTION_UPDATE    => _('Учебная сессия успешно обновлена'),
                self::ACTION_DELETE    => _('Учебная сессия успешно удалена'),
                self::ACTION_DELETE_BY => _('Учебные сессии успешно удалены')
            );
        }else{
            return array(
                self::ACTION_INSERT    => _('Учебный курс успешно создан'),
                self::ACTION_UPDATE    => _('Учебный курс успешно обновлён'),
                self::ACTION_DELETE    => _('Учебный курс успешно удалён'),
                self::ACTION_DELETE_BY => _('Учебные курсы успешно удалены')
            );
        }

    }


    public function setDefaults(Zend_Form $form, $newSession = false) {

        $subjectId = ( int ) $this->_request->getParam('subid', 0);

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        if ($subject) {

            if ($newSession) {
                $subject->type = HM_Subject_SubjectModel::TYPE_FULLTIME; // нужно обеспечить очный тип для сессий, т.к. в шаблоне уведомлений есть место и дата
                $subject->longtime = 0; // #10957#note-10

                $today = new HM_Date();
                $subject->begin = $today->toString('dd.MM.Y');
                $today->add(10, HM_Date::DAY);
                $subject->end = $today->toString('dd.MM.Y');
            } else {
                $subject->begin = $subject->getBegin();
                $subject->end = $subject->getEnd();
            }
            $values = $subject->getValues();

            if ($form->hasModifier('HM_Form_Modifier_BaseTypeSession')) {
                $values['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
            }

            $item = $this->getService('SubjectRoom')->fetchAll($this->getService('SubjectRoom')->quoteInto('cid = ?', $subjectId))->current();
            $values['rooms'] = $item->rid;
// теперь только одна аудитория
//            if (count($collection)) {
//                $values['rooms'] = array();
//                foreach($collection as $item) {
//                    $values['rooms'][$item->rid] = $item->rid;
//                }
//            }

            $accessElements = array();

            foreach(HM_Subject_SubjectModel::getFreeAccessElements() as $key => $value){
                if($key & $values['access_elements']){
                    $accessElements[] = $key;
                }
            }
            $values['access_elements'] = $accessElements;


/*            if($values['reg_type'] == HM_Subject_SubjectModel::REGTYPE_FREE){
                $values['reg_type'] = HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN;
            }*/

            if ($values['period_restriction_type'] == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                $begin = new HM_Date($values['begin']);
                $values['begin'] = $begin->toString('dd.MM.Y');
                $end = new HM_Date($values['end']);
                $values['end'] = $end->toString('dd.MM.Y');
            }

            /** @var HM_Form_Element_Vue_File $iconElement */
            $imageUrl = $this->view->publicFileToUrlWithHash($subject->getUserIcon());

            if ($imageUrl) {
                $iconElement = $form->getElement('icon');
                $iconElement->setPreviewImg($imageUrl);
            }

            $form->populate($values);
        }
    }

    public function update(Zend_Form $form)
    {
        $accessElements = 7;//0

        /*
         *
         * #7633

         foreach($form->getValue('access_elements') as $element){
            $accessElements = $accessElements | (int) $element;
        }
        */

        /*
        if($form->getValue('reg_type') == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && $form->getValue('claimant_process_id') == 0){
            $regType = HM_Subject_SubjectModel::REGTYPE_FREE;
        }else{
            $regType = $form->getValue('reg_type');
        }*/

        $regType = $form->getValue('reg_type');

        $periodRestrictionType = $form->getValue('period_restriction_type');

        $base = $form->getValue('base');
        $baseId = $form->getValue('base_id');
        $isFulltime = ($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION))
            ? $this->getService('Subject')->findOne($baseId)->is_fulltime
            : $form->getValue('is_fulltime');

        $values = array(
            'subid'                   => $form->getValue('subid'),
            'name'                    => $form->getValue('name'),
            'shortname'               => $form->getValue('shortname'),
            'supplier_id'             => $form->getValue('supplier_id'),
            'short_description'       => $form->getValue('short_description'),
            'description'             => $form->getValue('description'),
            'external_id'             => $form->getValue('external_id'),
            'code'                    => $form->getValue('code'),
            'is_fulltime'             => $isFulltime,
            // 'type'                    => $form->getValue('type'),
            'reg_type'                => $regType,
            'begin'                   => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
            'end'                     => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
            'begin_planned'           => $form->getValue('begin'),
            'end_planned'             => $form->getValue('end'),
            'longtime'                => $form->getValue('longtime'),
            'volume'                  => $form->getValue('volume'),
            'price'                   => sprintf("%.2f", $form->getValue('price')),
            'price_currency'          => $form->getValue('price_currency'),
            'plan_users'              => $form->getValue('plan_users'),
            'period'                  => $form->getValue('period'),
            'period_restriction_type' => $periodRestrictionType,
            'access_elements'         => $accessElements,
            'auto_done'               => $form->getValue('auto_done'),
            'base'                    => $base,
            'base_id'                 => $baseId,
            'claimant_process_id'     => $form->getValue('claimant_process_id'),
            'scale_id'                => $form->getValue('scale_id'),
            'auto_mark'               => $form->getValue('auto_mark'),
            'auto_graduate'           => $form->getValue('auto_graduate'),
            'formula_id'              => $form->getValue('auto_mark') ? $form->getValue('formula_id') : null,
            'threshold'               => $form->getValue('auto_mark') ? $form->getValue('threshold') :null,
            'in_banner'               => $form->getValue('in_banner'),
            'direction_id'            => $form->getValue('direction_id')
        );

        $subject = $this->getService('Subject')->update($values);
        $subjectId = $subject->subid;
        $this->getService('Subject')->linkClassifiers($subjectId, $form->getClassifierValues());

        $criteriaIds = $form->getValue('criteria');
        if (!is_array($criteriaIds)) $criteriaIds = array();
        $this->_assignCriteria($subject->subid, $criteriaIds, HM_At_Criterion_CriterionModel::TYPE_CORPORATE);

        $this->getService('Subject')->linkRoom($subjectId, $form->getValue('rooms'));

        $form->saveBannerIcon($subject->subid);
        $this->getService('Subject')->update(
            array(
                'subid' => $subject->subid,
                'banner_url' => $form->getElement('banner_url')->getValue()
            )
        );

        $banner = $form->getElement('icon_banner');

        if($banner->isUploaded()) {
            $bannerExtension = pathinfo($banner->getFileName())['extension'];
            $path = HM_Subject_SubjectModel::getIconFolder($subject->subid) . $subject->subid . '-full.' . $bannerExtension;
            $banner->addFilter('Rename', array(
                'target' => $path,
                'overwrite' => true,
            ));
            $banner->receive();
        }

        if ($form->getValue('icon') != null) {
            HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('icon'));
        } else {
            HM_Subject_SubjectService::updateIcon($subjectId, $form->getElement('server_icon'));
        }

        //Обрезаем все занятия выходящие за рамки курса
        if( $subject->period == HM_Subject_SubjectModel::PERIOD_DATES ) {
            $lessonService = $this->getService('Lesson');
            $lessonService->updateWhere(array('end' => $form->getValue('end') . ' 23:59:59'),
                $lessonService->quoteInto(array('CID = ?',' AND (end > ?',' OR end < ?)'),
                    array($subjectId,
                        $this->getService('Lesson')
                            ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                        $this->getService('Lesson')
                            ->getDateTime(strtotime($form->getValue('begin'))))));
            $lessonService->updateWhere(array('begin' => $form->getValue('begin') . ' 00:00:00'),
                $lessonService->quoteInto(array('CID = ?',' AND (begin > ?',' OR begin < ?)'),
                    array($subjectId,
                        $this->getService('Lesson')
                            ->getDateTime(strtotime($form->getValue('end') . ' 23:59:59')),
                        $this->getService('Lesson')
                            ->getDateTime(strtotime($form->getValue('begin'))))));
        }

        $this->getService('Subject')->cleanUpCacheForInfoblocks($subjectId);
    }

    public function delete($id)
    {
        $this->getService('Subject')->cleanUpCacheForInfoblocks($id);
        $this->getService('Subject')->delete($id);

        $this->getRequest()->setParam('subid', null);
        $this->getRequest()->setParam('subject_id', null);
    }

    public function create(Zend_Form $form) {

        $accessElements = 7; // 0

        /*
         * #7633
         * foreach($form->getValue('access_elements') as $element){
            $accessElements = $accessElements | (int) $element;
        }*/
/*
        if($form->getValue('reg_type') == HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN && $form->getValue('claimant_process_id') == 0){
            $regType = HM_Subject_SubjectModel::REGTYPE_FREE;
        }else{
            $regType = $form->getValue('reg_type');
        }*/

        $regType = $form->getValue('reg_type');


        $periodRestrictionType = $form->getValue('period_restriction_type');

        $base = $form->getValue('base');
        $baseId = $form->getValue('base_id');

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');

        $isFulltime = ($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION))
            ? $subjectService->findOne($baseId)->is_fulltime
            : $form->getValue('is_fulltime');

        $this->subject = $subject = $subjectService->insert(
            array(
                'name'                => $form->getValue('name'),
                'shortname'           => $form->getValue('shortname'),
                'short_description'   => $form->getValue('short_description'),
                'description'         => $form->getValue('description'),
                'external_id'         => $form->getValue('external_id'),
                'code'                => $form->getValue('code'),
                'type'                => HM_Tc_Subject_SubjectModel::TYPE_DISTANCE,
                'reg_type'            => $regType,
//                'begin'               => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('begin') : null,
//                'end'                 => ($periodRestrictionType != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) ? $form->getValue('end') : null,
                'begin'               => $form->getValue('begin'),
                'end'                 => $form->getValue('end'),
                'longtime'            => $form->getValue('longtime'),
                'volume'              => $form->getValue('volume'),
                'price'               => sprintf("%.2f", $form->getValue('price')),
                'price_currency'      => $form->getValue('price_currency'),
                'plan_users'          => $form->getValue('plan_users'),
                'period'              => $form->getValue('period'),
                'period_restriction_type' => $periodRestrictionType,
                'access_elements'     => $accessElements,
                'auto_done'           => $form->getValue('auto_done'),
                'base'                => $base,
                'base_id'             => $baseId,
                'base_color'          => ($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION))?  $subjectService->getSubjectColor($baseId) : $subjectService->generateColor(),
                'claimant_process_id' => $form->getValue('claimant_process_id'),
                'scale_id'            => $form->getValue('scale_id'),
                'auto_mark'           => $form->getValue('auto_mark'),
                'auto_graduate'       => $form->getValue('auto_graduate'),
                'formula_id'          => $form->getValue('auto_mark') ? $form->getValue('formula_id') : null,
                'threshold'           => $form->getValue('auto_mark') ? $form->getValue('threshold') : null,
                'in_banner'           => $form->getValue('in_banner'),
                'status'              => 1, // Нет константиы?
                'provider_id'         => HM_Tc_Provider_ProviderModel::HARDCODED_ID_INTERNAL_STUDY,
                'category'            => $this->isLaborSafety ? HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY : null, // иначе в сессия планирования не попадает в выборку?
                'direction_id'        => $form->getValue('direction_id'),
                'is_labor_safety'     => $this->isLaborSafety,
                'created'             => date('Y-m-d H:i:s'),
                'is_fulltime'         => $isFulltime,
            )
        );

        $form->saveBannerIcon($subject->subid);
        $subjectService->update(
            array(
                'subid' => $subject->subid,
                'banner_url' => $form->getElement('banner_url')->getValue()
            )
        );

        $subjectService->cleanUpCacheForInfoblocks($subject->subid);

        if ($baseId && ($base == HM_Subject_SubjectModel::BASETYPE_SESSION)) {

            try {
                $subjectService->copyElements($baseId, $subject->subid);
                $subjectService->copyImage($baseId, $subject->subid);
            } catch (HM_Exception $e) {
                // что-то не скопировалось..(
            }

            // апдейтим родительский уч.курс - убираем ограничения по времени и месту
            // автоназначение basetype
            $changes = [
                'base' => HM_Subject_SubjectModel::BASETYPE_BASE,
                'period' => HM_Subject_SubjectModel::PERIOD_FREE,
// БП отключен, не надо больше менять тип согласования
// 'claimant_process_id' => array_shift(HM_Subject_SubjectModel::getTrainingProcessIds()),
            ];
            $subjectService->updateWhere($changes, array('subid = ?' => $baseId));
            $subjectService->unlinkRooms($baseId);
        }

        $this->expandResponsibility($subject);

        $classifiers = $form->getClassifierValues();
        $this->getService('Classifier')->unlinkItem($subject->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($subject->subid, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                }
            }
        }

        $criteriaIds = $form->getValue('criteria');
        if (!is_array($criteriaIds)) $criteriaIds = [];
        $this->_assignCriteria($subject->subid, $criteriaIds, HM_At_Criterion_CriterionModel::TYPE_CORPORATE);

        $roomId = $form->getValue('rooms');
        $subjectService->linkRoom($subject->subid, $roomId);

        // Если это создание сессии, то выше мы скопировали картинку из базового курса, а тут проверяем, не изменили ли её
        $photo = $form->getElement('icon');
        if($photo->isUploaded()){
            $src = $photo->getFileName();
            $ext = '.jpg';
// @todo: решить по-правильному, хранить имя файла + расширение
//            if (preg_match('/(\.[^\.]+)\s*$/i', $src, $m)) {
//                $ext = $m[1];
//            }
            $path = HM_Subject_SubjectModel::getIconFolder($subject->subid) . $subject->subid . $ext;

            if (@rename($src, $path) ) {
//                $img = PhpThumb_Factory::create($path);
//                // костыль для виджета subjectSlider
//                $img->adaptiveResize(HM_Subject_SubjectModel::THUMB_WIDTH, HM_Subject_SubjectModel::THUMB_HEIGHT);
//                $img->save($path);
            }

        }

        $banner = $form->getElement('icon_banner');
        if($banner->isUploaded()){
            $path = HM_Subject_SubjectModel::getIconFolder($subject->subid) . $subject->subid . '-full.jpg';
            $banner->addFilter('Rename', array(
                'target' => $path,
                'overwrite' => true,
            ));
            $banner->receive();
        }

        if($this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ))) {
            $currentUserId = $this->getService('User')->getCurrentUserId();
            $userSubjectIds = $this->getService('Responsibility')->getSubjectIds($currentUserId);
            $userSubjectIds[] = $subject->subid;
            $this->getService('Responsibility')->set(
                $currentUserId,
                HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT,
                $userSubjectIds
            );
        }

    }

    public function cardAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($subjectId));

        $this->view->title =  $subject->getName();
        $this->view->photo = $subject->getUserIcon();
        $this->view->defaultPhoto = $subject->getDefaultIcon();
        $this->view->fields = $subject->getCardFieldsValue($this->fromProgramArray);
        $this->view->subject = $subject;

    }

    public function descriptionAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $userId = (int)$this->getService('User')->getCurrentUserId();

        $this->view->isStudent = $this->getService('Subject')->isStudent($subjectId, $userId);
        $this->view->subjectId = $subjectId;

        $subject = $this->view->subject = $this->getService('Subject')->getOne(
            $this->getService('Subject')->find($subjectId)
        );

        $this->view->clType = $this->_getParam('type', 0);
        $this->view->clItem = $this->_getParam('item', 0);
        $this->view->clClassifierId = $this->_getParam('classifier_id', 0);

        $this->view->regText = $subject->claimant_process_id == HM_Subject_SubjectModel::APPROVE_NONE ? _('Начать обучение') : _('Подать заявку');

        $this->view->setHeader($this->view->subject->name);
        /*if($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_GUEST){
            $this->view->regText = _('Зарегистрироваться');
        }*/


    }

    public function updateType($type)
    {
        $types = HM_Subject_SubjectModel::getTypes();
        return $types[$type];
    }

    public function updateStudents($students, $subject_id)
    {
        return '<a href="' . $this->view->url(['module' => 'assign', 'controller' => 'student', 'action' => 'index', 'gridmod' => null, 'subject_id' => $subject_id]) . '" title="' . _('Список слушателей') . '">' . $students . '</a>';
    }

    public function updateDateBegin($date, $period, $periodRestrictionType)
    {
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return '-';
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return _('Дата назначения');
            default:
                return $date;
        }
    }

    public function updateDateEnd($date, $period, $longtime, $periodRestrictionType)
    {
        $date = $this->getDateForGrid($date);
        switch ($period) {
            case HM_Subject_SubjectModel::PERIOD_FREE:
                return '-';
            case HM_Subject_SubjectModel::PERIOD_FIXED:
                return sprintf(_('Через %s дней'), $longtime);
            default:
                if ($periodRestrictionType == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
                    $date .= HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Плановая дата. Фактически начало/окончание обучения по курсу определяется тьютором'), 1);
                }
                return $date;
        }
    }

    public function classifiersCache($field, $select)
    {
        $fields = array_unique(explode(',', $field));
        $result = (is_array($fields) && (count($fields) > 1))
            ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Classifier')->pluralFormCount(count($fields)) . '</p>')
            : array();

        foreach ($fields as $value) {
            $result[] = "<p>{$value}</p>";
        }

        if ($result)
            return implode(' ', $result);
        else
            return _('Нет');
    }

    protected function _filterCachedClassifiers($id)
    {
        return $this->classifierCache->exists('classifier_id', $id);
    }

    public function editAction()
    {
        if ($subid = $this->_getParam('subject_id')) {
            $this->_setParam('subid', $subid);
        }
        $base = (int) $this->getParam('base', HM_Subject_SubjectModel::BASETYPE_PRACTICE);
        $subject = $this->getService('Subject')->findOne($this->_getParam('subid'));
        $this->view->setHeader($subject->name);
        $this->view->setSubSubHeader(_('Редактирование учебного курса'));
        $this->view->setBackUrl($this->view->url([
            'module' => 'subject',
            'controller' => 'list',
            'action' => 'index',
            // 'base' => $subject->base,
            'base' => $base,
        ], null, true));

        $this->initContext($subject);
        $this->view->addSidebar('subject', [
            'model' => $subject,
        ]);

        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getPost();
            // @todo: убрать этот хак после решения #31891
            if (empty($post['scale_id'])) $post['scale_id'] = HM_Scale_ScaleModel::TYPE_CONTINUOUS;

            if ($form->isValid($post)) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
                $this->_redirectToIndex();
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_CRIT,
                    'message' => _('Внимание! Не все поля заполнены корректно!'),
                ));
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function copyAction()
    {
        $subid = (int) $this->_getParam('subid', 0);
        if ($subid) {
            $subject = $this->getService('Subject')->copy($subid);

        $this->expandResponsibility($subject);

            if ($subject) {
                if($this->_form->hasModifier('HM_Form_Modifier_BaseTypeSession')){
                    $this->_flashMessenger->addMessage(_('Учебная сессия успешно скопирована.'));
                }else{
                    $this->_flashMessenger->addMessage(_('Учебный курс успешно скопирован.'));
                }
            }
        }

        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher' => 'index', 'subject_id' => $this->_getParam('subject_id', null), 'base' => $this->_getParam('base', 0)));
        //$this->_redirectToIndex();
    }

    public function copyFromBaseAction()
    {
        $subid = (int) $this->_getParam('subid', 0);
        if ($subid) {
            $collection = $this->getService('Subject')->find($subid);
            if (count($collection)) {
                $subject = $collection->current();
                if ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION) {
                    try {
                        $this->getService('Subject')->copyElements($subject->base_id, $subject->subid);
                    } catch (HM_Exception $e) {
                        // что-то не скопировалось..(
                    }
                    $this->_flashMessenger->addMessage(_('Содержимое базового курса успешно скопировано.'));
                }
            }
        }
        $this->_redirector->gotoSimple('index', 'list', 'subject', array('switcher' => 'index', 'subject_id' => $this->_getParam('subject_id', null), 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION));
        //$this->_redirectToIndex();
    }


    public function updateBaseId($baseId, $select)
    {
        if($this->_baseIds == null){
            $fetchAll = $select->query()->fetchAll();
            $baseIds = array(0);
            foreach($fetchAll as $one){
                $baseIds[] = $one['base_id'];
            }
            $this->_baseIds = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $baseIds));
        }

        $subject = $this->_baseIds->exists('subid', $baseId);

        if($subject){
            $url = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $baseId));
            $result = "<a href='{$url}'>{$subject->name}</a>";
        }else{
            $result = 'Нет';
        }

        return $result;
    }

    public function sessionsCache($field, $select){
        $result = [];
        if ($this->sessionsCache === array()){
            foreach ($this->getService('Subject')->fetchAll(array('base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION)) as $session) {
                $this->sessionsCache[$session->base_id][$session->subid] = $session->name;
            }
        }

        if (isset($this->sessionsCache[$field]) && ($count = count($this->sessionsCache[$field]))) {
            $result = array('<p class="total">' . sprintf(_n('сессия plural', '%s сессия', $count), $count) . '</p>');
            foreach($this->sessionsCache[$field] as $subid => $name){
                $url = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subid));
                $result[] = "<p><a href='{$url}'>{$name}</a></p>";
            }
        }

        if (count($result))
            return implode('',$result);
        else
            return _('Нет');
    }

    public function updateOffline($id, $date)
    {

        if ($id) {
            $url = $this->view->url(array(
                'module' => 'offline',
                'controller' => 'list',
                'action' => 'download',
                'id' => $id,
            ));
            return "<a href='{$url}'>{$date}</a>";
        }
        return _('Нет');
    }

    public function updateRating($rating)
    {
        $percent = round((int)$rating * 100);
        return $percent.'%';
    }

    public function colorAction()
    {
        $subs = $this->getService('Subject')->fetchAll()->getList('subid');

        foreach ($subs as $subid) {
            $data = array(
                'subid' => (int)$subid,
                'base_color' => $this->getService('Subject')->generateColor()
            );
            $this->getService('Subject')->prUpdate($data);
        }
    }

    protected function addModifier($form)
    {
        $baseType = ($this->_subject && ($this->baseType != HM_Subject_SubjectModel::BASETYPE_SESSION))
                    ? $this->_subject->getBaseType()
                    : $this->baseType;
        switch ($baseType) {
            case HM_Subject_SubjectModel::BASETYPE_BASE:
                $form->addModifier(new HM_Form_Modifier_BaseTypeBase());
                break;

            case HM_Subject_SubjectModel::BASETYPE_PRACTICE:
                $form->addModifier(new HM_Form_Modifier_BaseTypePractice());
                break;

            case HM_Subject_SubjectModel::BASETYPE_SESSION:
                $form->addModifier(new HM_Form_Modifier_BaseTypeSession());
                break;
        }
    }

    public function deleteAction()
    {
        $params = $this->_getAllParams();
        foreach($params as $key => $value) {
            if (substr($key, -3) == '_id') {
                $this->_setParam('id', $value);
                break;
            }

            if (in_array($key, array('subid', 'projid'))) { // hack
                $this->_setParam('id', $value);
            }
        }

        $id = (int) $this->_getParam('id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE));
        }

        $this->_redirectToIndex(false);
    }

    protected function _redirectToIndex($description = true)
    {
        $subjectId = (int) $this->_getParam('subject_id',
            $this->_getParam('subid',
                ($this->subject ? $this->subject->subid : 0)
            )
        );

        if ($subjectId && $description) {
            $this->_redirector->gotoSimple('description', 'index', 'subject', ['subject_id' => $subjectId]);
        }

        if ($this->_getParam('base') == HM_Subject_SubjectModel::BASETYPE_SESSION) {
            $this->_redirector->gotoUrl($this->view->url([
                    'action' => 'index',
                    'controller' => 'list',
                    'module' => 'subject',
                    'base' => HM_Subject_SubjectModel::BASETYPE_SESSION,
                    'subid' => null
                ]) . '/?page_id=m0607');
        } else {
            $this->_redirector->gotoUrl($this->view->url([
                    'action' => 'index',
                    'controller' => 'list',
                    'module' => 'subject',
                    'base' => HM_Subject_SubjectModel::BASETYPE_PRACTICE,
                    'subid' => null
                ]) . '/?page_id=m0607');
        }

//        $this->_redirector->gotoSimple('index');
    }

    protected function _assignCriteria($subjectId, $criteriaIds = [], $criteriaType = HM_At_Criterion_CriterionModel::TYPE_CORPORATE)
    {
        $subjectCriteria = $this->getService('SubjectCriteria')->fetchAll(array('subject_id = ?' => $subjectId, 'criterion_type = ?' => $criteriaType))->getList('criterion_id');

        $forInsert = array_diff($criteriaIds, $subjectCriteria);
        $forDelete = array_diff($subjectCriteria, $criteriaIds);

        if (!empty($forDelete)) {
            foreach ($forDelete as $id) {
                $this->getService('SubjectCriteria')->deleteBy(array(
                    'subject_id = ?' => $subjectId,
                    'criterion_id = ?' => $id,
                    'criterion_type = ?' => $criteriaType
                ));
            }
        }

        if(!empty($forInsert)) {
            foreach ($forInsert as $id) {
                $this->getService('SubjectCriteria')->insert(array(
                    'subject_id' => $subjectId,
                    'criterion_id' => $id,
                    'criterion_type' => $criteriaType
                ));
            }
        }
    }

    protected function setSubjectId()
    {
        $this->subjectId = (int) $this->_getParam('subject_id', $this->_getParam('subid', 0));
    }

    protected function setSubject()
    {
        $this->subject = $this->subjectId ? $this->getService('Subject')->findOne($this->subjectId) : false;
    }

    protected function setIsLaborSafety()
    {
        $this->isLaborSafety = $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(), [
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
        ]) ? 1 : 0;
    }

    protected function setBaseType()
    {
        $this->baseType = $this->_getParam('base', $this->subject ? $this->subject->getBaseType() : false);
    }

    protected function setSwitcher()
    {
//        $this->switcher = $this->_getParam('switcher', '');
        $this->switcher = $this->getSwitcherSetOrder(null, 'name_ASC');

        if ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), [
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
            ]) && $this->switcher == 'programm') {
            $this->switcher = '';
        }

        $disabledSwitcherMods = [];
        if ($this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(), [
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
        ])) {
            // Если пользователь не записан на программы, то сбросить switcher
            if (!count($this->getService('Programm')->getUserElsProgramms($this->getService('User')->getCurrentUserId()))) {
                $disabledSwitcherMods[] = 'programm';
                $this->switcher = 'list';
            }
        } elseif ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), [
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
            ]) && $this->switcher == '') {
            $this->switcher = 'index';
            $disabledSwitcherMods[] = 'programm';
        } /*elseif ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), [
                HM_Role_Abstract_RoleModel::ROLE_TEACHER
            ]) && $this->switcher == '') {
            $this->switcher = 'list';
        }*/

        //if (empty($this->switcher)) $this->switcher = 'list';

        $this->view->disabledSwitcherMods = $disabledSwitcherMods;
    }

    public function criteriaAction()
    {
        $criteria = $this->getService('AtCriterion')->fetchAll();

        $subjectId = $this->_getParam('subject_id');
        $criteriaType = $this->_getParam('criterion_type');

        $selectedCriteria = false;
        if ($subjectId) {
            $selectedCriteria = $this->getService('SubjectCriteria')->fetchAll(array('subject_id = ?' => $subjectId, 'criterion_type = ?' => $criteriaType))->getList('criterion_id');
        }

        $list = [];
        $position = 0;
        foreach($criteria as $criterion) {
            $list[] = [
                'criterion_id' => $criterion->criterion_id,
                'name' => $criterion->name,
                'selected' => (bool)($selectedCriteria && in_array($criterion->criterion_id, $selectedCriteria)),
                'level' => 1,
                'lft' => ++$position,
                'rgt' => ++$position,
            ];
        }

        die(json_encode($list));
    }

}

