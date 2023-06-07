<?php
class Session_UsersController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;

    protected $tagsCache = null;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $this->_currentPosition = $this->getService('User')->isManager(false, true);
            if (
                $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) &&
                !$this->_currentPosition
            ) {
                $this->view->addContextNavigationModifier(new HM_Navigation_Modifier_Remove_Page('resource', 'cm:atsession:page2'));
            }
        }
    }

    public function indexAction()
    {
        $courseId = (int) $this->_getParam('course_id', 0);
        $grid = $this->_grid;
        $url = array('action' => 'assign');
        if ($courseId > 0) {
            $url['courseId'] = $courseId;
        }

        if ($this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_CURATOR
        ))) {
            // заголовок действия назначения на курс в зависимости от контроллера
            switch ( Zend_Controller_Front::getInstance()->getRequest()->getControllerName() ){
                case 'teacher':
                    if ($this->getService('Acl')->inheritsRole(
                        $this->getService('User')->getCurrentUserRole(), array(
                        HM_Role_Abstract_RoleModel::ROLE_TEACHER
                    ))) break;
                    $assignMenuItem   = ( $courseId > 0 ) ? _('Назначить преподавателей на курс')   : _('Назначить преподавателей на курсы');
                    $unassignMenuItem = ( $courseId > 0 ) ? _('Отменить назначение преподавателей') : _('Отменить назначения преподавателей');
                    break;
                case 'student':
                    $subject = $this->_subject;
                    if (!$subject || ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED)) {
                        $assignMenuItem   = ( $courseId > 0 ) ? _('Назначить слушателей на курс')   : _('Назначить слушателей на курсы');
                        $unassignMenuItem = ( $courseId > 0 ) ? _('Отменить назначение слушателей') : _('Отменить назначения слушателей');
                    }
                    break;
                case 'participant':
                    $subject = $this->view->getParam('subject');
                    if (!$subject || ($subject->state != HM_Project_ProjectModel::STATE_CLOSED)) {
                        $assignMenuItem   = ( $courseId > 0 ) ? _('Назначить участников на конкурс') : _('Назначить участников на конкурсы');
                        $unassignMenuItem = ( $courseId > 0 ) ? _('Отменить назначение участников')  : _('Отменить назначения участников');
                    }
                    break;
                default:
                    if ($this->getService('Acl')->inheritsRole(
                        $this->getService('User')->getCurrentUserRole(),
                        HM_Role_Abstract_RoleModel::ROLE_TEACHER
                    )) break;
                    $assignMenuItem   = ( $courseId > 0 ) ? _('Назначить на курс')           : _('Назначить на курсы');
                    $unassignMenuItem = ( $courseId > 0 ) ? _('Отменить назначение на курс') : _('Отменить назначение на курсы');
                    break;
            }

            if ($assignMenuItem) {
                $grid->addMassAction(
                    $url,
                    $assignMenuItem,
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }

            $url = array('action' => 'unassign');
            if ($courseId > 0) {
                $url['course_id'] =
                $url['courseId' ] = $courseId;
            }

            if ($unassignMenuItem) {
                $grid->addMassAction(
                    $url,
                    $unassignMenuItem,
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }
        }

        $coursesPrompt = _('Выберите курс');
        $userId = $this->getService('User')->getCurrentUserId();
        //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
        $collection = $this->getService('Dean')->getActiveSubjectsResponsibilities($userId);
        $full_collection = $this->getService('Dean')->getSubjectsResponsibilities($userId);

        if ($courseId <= 0) {
            if (count($collection)) {
                $courses = $collection->getList(
                    'CID',
                    'Title',
                    $coursesPrompt
                );
            }

            if (count($full_collection)) {
                $all_courses = $full_collection->getList(
                    'CID',
                    'Title',
                    $coursesPrompt
                );
            }
            $grid->addSubMassActionSelect(
                array(
                    $this->view->url(array('action' => 'assign'))
                ),
                'courseId[]',
                $courses
            );
            $grid->addSubMassActionSelect(
                array($this->view->url(array('action' => 'unassign'))
                ),
                'unCourseId[]',
                $all_courses
            );
        }

        $grid->addAction(array(
            'module' => 'message',
            'controller' => 'send',
            'action' => 'index'
        ),
            array('MID'),
            _('Отправить сообщение')
        );

        $grid->addMassAction(
            array(
                'module'     => 'message',
                'controller' => 'send',
                'action'     => 'index'
            ),
            _('Отправить сообщение')
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function positionFullFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){

            $value = '%' . $value . '%';

            $select->where("(so.name LIKE ?", $value);
            $select->orWhere("so1.name LIKE ?", $value);
            $select->orWhere("so2.name LIKE ?)", $value);


        }

    }

    public function listAction()
    {
        $gridId = 'grid';

        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", $sorting = 'fio_ASC');
        }

        $select = $this->getService('AtSessionUser')->getSelect();

        $select->from(array('asu' => 'at_session_users'), array(
            'MID' => 'p.MID',
            'soid' => 'so.soid',
            'session' => 'asu.session_id',
            'session_user_id' => 'asu.session_user_id',
            'workflow_id' => 'asu.session_user_id',
            'ase.user_id',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
//            'position_full' => new Zend_Db_Expr("CONCAT(so.name, CONCAT('/', CONCAT(so1.name, CONCAT('/', so2.name))))"),
//            'department' => 'so.owner_soid',
            'is_manager' => 'so.is_manager',
            'position' => 'so.name',
            'events' => new Zend_Db_Expr("COUNT(DISTINCT ase.session_event_id)"),
            'asu.status',
            'status_id' => 'asu.status',
            'session_name' => 'ass.name',
            'session_begin_date' => 'ass.begin_date',
            'tags' => 'p.MID',
        ))
            ->joinInner(array('p' => 'People'), 'p.MID = asu.user_id', array())
            ->joinInner(array('so' => 'structure_of_organ'), 'so.soid = asu.position_id', array())
            ->joinInner(array('so1' => 'structure_of_organ'), "so.owner_soid = so1.soid", array())
            ->joinInner(array('so2' => 'structure_of_organ'), "so1.owner_soid = so2.soid", array())
            ->joinInner(array('ass' => 'at_sessions'), 'ass.session_id = asu.session_id AND programm_type ='.HM_Programm_ProgrammModel::TYPE_ASSESSMENT, array())
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_id = asu.session_id AND ase.user_id = p.MID', array())
            ->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'ase.session_user_id', 'asu.session_user_id', 'so.owner_soid', 'so.soid', 'so.is_manager', 'so.name', 'so1.name', 'so2.name', 'ase.user_id', 'asu.status', 'asu.session_id', 'ass.name', 'ass.begin_date'));


        if ($this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $department = $this->getService('Orgstructure')->getDefaultParent();
            $select->where('so.lft > ?', $department->lft)
                ->where('so.rgt < ?', $department->rgt);
        } elseif ($this->currentUserRole(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL)) {
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("so.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        $grid = $this->getGrid($select, array(
            'MID' => array('hidden' => true),
            'soid' => array('hidden' => true),
            'status_id' => array('hidden' => true),
            'session_user_id' => array('hidden' => true),
            'session' => array('hidden' => true),
            'workflow_id' => array('hidden' => true),
            'user_id' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'decorator' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) ? $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}') . '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'gridmod' => null, 'user_id' => '', 'baseUrl' => ''), null, true) . '{{MID}}'.'">'. '{{fio}}</a>' : null,
            ),
//            'position_full' => array('title' => _('Должность')),
//            'department' => array(
//                'title' => _('Подразделение'),
//                'callback' => array(
//                    'function'=> array($this, 'departmentsCache'),
//                    'params' => array('{{department}}', $select)
//                )
//            ),
            'position_id' => array('hidden' => true),
            'is_manager' => array('hidden' => true),
            'position' => array(
                'title' => _('Должность'),
                'callback' => array(
                    'function' => array($this, 'updatePositionName'),
                    'params' => array(
                        '{{position}}',
                        '{{soid}}',
                        HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                        '{{is_manager}}'
                    )
                )
            ),
            'session_name' => array(
                'title' => _('Имя сессии'),
                'callback' => array(
                    'function'=> array($this, 'updateSession'),
                    'params' => array('{{session}}', '{{session_name}}')
                )
            ),
            'session_begin_date' => array(
                'title' => _('Дата начала сессии'),
                'format' => 'date',
            ),
            'events' => array(
                'title' => _('Количество оценочных форм'),
                'callback' => array(
                    'function'=> array($this, 'updateEvents'),
                    'params' => array('{{events}}', '{{fio}}', '{{user_id}}', '{{session}}')
                )
            ),
            'status' => array(
                'title' => _('Статус'),
                'callback' => array(
                    'function' => array($this, 'updateStatus'),
                    'params' => array('{{status}}')
                )
            ),
            'tags' => array(
                'title' => _('Метки')
            ),
        ),
            array(
                'fio' => null,
//                'department' => null,
                'position' => array(
                    'render' => 'department'
                ),

//                'position_full' =>  array(
//                    'render' => 'department',
//                    'callback' => array(
//                        'function'=>array($this, 'positionFullFilter'),
//                        'params'=>array()
//                    )
//                ),
                'status' => array('values' => HM_At_Session_User_UserModel::getStatuses()),
                'session_begin_date' => array('render' => 'Date'),
                'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
            ), $gridId);

            $grid->addAction(array(
                'baseUrl' => '',
                'module' => 'message',
                'controller' => 'send',
                'action' => 'index'
            ),
                array('MID'),
                _('Отправить сообщение')
            );

/*
        $grid->addAction(array(
            'module' => 'session',
            'controller' => 'report',
            'action' => 'user-word',
        ),
            array('session_user_id'),
            _('Скачать индивидуальный отчёт')
        );
*/

        $grid->addAction(array(
            'module' => 'session',
            'controller' => 'report',
            'action' => 'user',
        ),
            array('session_user_id'),
            _('Индивидуальный отчёт online')
        );

        $grid->addAction(array(
            'module' => 'session',
            'controller' => 'report',
            'action' => 'user-analytics',
        ),
            array('session_user_id'),
            _('Анализ результатов')
        );



        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $grid->addAction(array(
                'baseUrl' => '',
                'module' => 'user',
                'controller' => 'list',
                'action' => 'login-as'
            ),
                array('MID'),
                _('Войти от имени пользователя'),
                _('Вы действительно хотите войти в систему от имени данного пользователя? При этом все функции Вашей текущей роли будут недоступны. Вы сможете вернуться в свою роль при помощи обратной функции "Выйти из режима". Продолжить?') // не работает??
            );
        }

        $grid->addMassAction(array('action' => 'assign-tag'), _('Назначить метку'));
        $grid->addMassAction(array('action' => 'unassign-tag'), _('Отменить назначение меток'));
        $grid->addSubMassActionFcbk($this->view->url(array('action' => 'assign-tag')),
            'tags', array('AllowNewItems' => true));
        $grid->addSubMassActionFcbk(array($this->view->url(array('action' => 'unassign-tag'))),
            'tagsUnassign');

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array(
                    '{{MID}}',
                    $this->getService('TagRef')->getUserType(),
                    $grid
                )
            )
        ));

        $this->view->grid = $grid;
    }

    public function assignTagAction()
    {
        $postMassIds = explode(',', $this->_request->getParam('postMassIds_grid'));

        $ids = array();
        foreach ($postMassIds as $id) {
            $atSessionUser = $this->getService('AtSessionUser')->find($id)->current();
            $ids[] = $atSessionUser->user_id;
        }

        $tags = array_unique($this->_getParam('tags', array()));
        $tagsCache = $this->getService('Tag')->getTagsCache($ids, $this->getService('TagRef')->getUserType());

        foreach ($ids as $userId) {
            if (!isset($tagsCache[$userId])) $tagsCache[$userId] = array();
            $this->getService('Tag')->updateTags(($tagsCache[$userId] + $tags), $userId, $this->getService('TagRef')->getUserType());
            $this->getService('StudyGroup')->addUserByTags($userId, $tags, false);
        }

        $this->_flashMessenger->addMessage(_('Метка успешно назначена пользователям'));
        $this->_redirector->gotoSimple('list', 'users', 'session');

    }

    public function unassignTagAction()
    {
        $postMassIds = explode(',', $this->_request->getParam('postMassIds_grid'));

        $ids = array();
        foreach ($postMassIds as $id) {
            $atSessionUser = $this->getService('AtSessionUser')->find($id)->current();
            $ids[] = $atSessionUser->user_id;
        }

        $tags = array_unique($this->_getParam('tagsUnassign', array()));

        $tagsCache = $this->getService('Tag')->getTagsCache($ids, $this->getService('TagRef')->getUserType());

        foreach ($ids as $userId) {

            if (!isset($tagsCache[$userId])) {
                $tagsCache[$userId] = array();
            }

            foreach ($tags as $tag) {
                if ($this->getService('Tag')->isNewTag($tag)) continue;
                if (isset($tagsCache[$userId][$tag])) {
                    unset($tagsCache[$userId][$tag]);
                }
            }
            $this->getService('Tag')->updateTags($tagsCache[$userId], $userId, $this->getService('TagRef')->getUserType());
        }

        $this->_flashMessenger->addMessage(_('Назначение меток пользователям отменено'));
        $this->_redirector->gotoSimple('list', 'users', 'session');

    }

    public function displayTags($itemId, $itemType, $grid)
    {
        $tagService = $this->getService('Tag');

        if ($this->tagsCache === null) {
            $result = $grid->getResult();
            $mids = array();

            foreach ($result as $raw) {
                $mids[$raw['MID']] = $raw['MID'];
            }

            $this->tagsCache = $tagService->getTagsCache($mids, $itemType);
        }

        $arResult = isset($this->tagsCache[$itemId]) ? $this->tagsCache[$itemId] : array();

        if (!count($arResult)) {
            return '';
        }

        asort($arResult);

        //форматирование в раскрывающийся список

        $txt = (count($arResult) > 1) ? '<p class="total">'. $tagService->pluralTagCount(count($arResult)) . '</p>' : '';

        foreach ($arResult as $item) {
            $txt .= "<p>$item</p>";
        }

        return $txt;
    }

    public function updateEvents($events, $fio, $user_id, $session)
    {
        if (!$this->getService('Acl')->isCurrentAllowed('mca:session:user:change-status')) return $events;

        $url = $this->view->url(array(
            'action' => 'list',
            'controller' => 'event',
            'module' => 'session',
            'gridmod' => 'ajax', // только так фильтр устанавливается; надо бы убрать этот костыль
            'usergrid' => $fio,
            'session_id' => $session,
        ));
        $title = _('Список анкет');
// Счетчик учитывал "необычную" форму (она коллективная, в отличие от остальных) для парных сравнений. Предположение - форма нужна для респондента, по привязывать ее к участнику неправильно,
// тем не менее ее привязывыают к одному из участников, но "не совсем", оставляют пустую ссылку на чела - на нее и ориентируемся (ее вычитаем)
        return "<a href='{$url}' title='{$title}'>".($user_id ? $events : (max(0,$events-1)))."</a>";
//        return "<a href='{$url}' title='{$title}'>{$events}</a>";
    }

    /**
     * Создаёт ссылку на сессию
     * @param string $session_id
     * @param string $session_name
     * @return string
     */
    public function updateSession($session_id, $session_name)
    {
        $url = $this->view->url(array(
            'action' => 'card',
            'controller' => 'index',
            'module' => 'session',
            'session_id' => $session_id
        ));
        return "<a href='{$url}'>".$session_name."</a>";
    }

    public function updateStatus($status)
    {
        return HM_At_Session_User_UserModel::getStatus($status);
    }

    public function printWorkflow($sessionUserId, $sessionId)
    {
        $session = $this->getService('AtSession')->find($sessionId)->current();
        switch ($session->state){
            case 1:
                return _('начат');
            case 2:
                return _('закрыт');
            default:
                return _('не начат');
        }
    }

    public function assignAction(){}

    protected function _preAssign($personId, $courseId){}
    protected function _assign($personId, $courseId) {}
    protected function _postAssign($personId, $courseId){}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishAssign(){}
    protected function _finishUnassign(){}

    public function unassignAction(){}
    protected function _unassign($personId, $courseId){}
    protected function _preUnassign($personId, $courseId){}
}
