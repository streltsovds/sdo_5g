<?php
class Assign_ModeratorController extends HM_Controller_Action_Assign
{
    use HM_Controller_Action_Trait_Grid;
    use HM_Controller_Action_Trait_Context;
    use HM_Grid_ColumnCallback_Trait_Common;

    protected $service     = 'Project';
    protected $idParamName = 'project_id';
    protected $idFieldName = 'projid';
    protected $id          = 0;

    protected $courseCache = array();
    protected $_cache = array();

    /**@var  $_serviceProject HM_Project_ProjectService | null*/
    protected $_serviceProject = null;
    protected $_hasErrors = false;
    protected $_expiredProjectsNames = array();
    protected $_cacheProjectExpire = array();
    protected $_cacheProjectTitle = array();
    protected $_periodRestrictionType;

    public function init()
    {
        parent::init();

        if (!$this->isAjaxRequest()) {
            $projectId = (int) $this->_getParam('project_id', 0);
            if ($projectId) { // Делаем страницу расширенной
                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $project = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $project
                    )
                );
            }
        }
    }

    public function indexAction()
    {
        $courseId = (int) $this->_getParam('project_id', 0);
        $switcher = $this->getSwitcherSetOrder($courseId, 'fio_ASC', 'notempty DESC');

        $from = array (
            'MID',
            //'dublicate', //ВНИМАНИЕ при мерже !! Дубликаты на этой странице НЕ должны выводиться; 
            'notempty' => "CASE WHEN (t1.LastName IS NULL AND t1.FirstName IS NULL AND  t1.Patronymic IS NULL) OR (t1.LastName = '' AND t1.FirstName = '' AND t1.Patronymic = '') THEN 0 ELSE 1 END",
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
            'departments' => new Zend_Db_Expr('GROUP_CONCAT(d.owner_soid)'),
            'positions' => new Zend_Db_Expr('GROUP_CONCAT(d.soid)'),
        );

        if (!$courseId){
            $from['projects'] = new Zend_Db_Expr('GROUP_CONCAT(t2.project_id)');
        } else {
            $from['period_restriction_type'] = 't4.period_restriction_type';
        }

        $from['tags'] = 't1.MID';

        $select = $this->getService('User')->getSelect();
        // ВНИМАНИЕ! При мерже с Билайном поле departments не нужно! У билайна уже есть соотв.столбцы
        $select->from(
            array('t1' => 'People'),
            $from
        )
        ->joinLeft(array('d' => 'structure_of_organ'),
            'd.mid = t1.MID',
            array()
        );

        if (!$switcher){
            $select->joinInner(
                array('t2' => 'moderators'),
                't1.MID = t2.user_id',
                array()
            );
        }
       
        if ($courseId > 0) {
            $subSelect = $this->getService('User')->getSelect()
                ->from(
                    array('t1' => 'moderators'),
                    array('user_id', 'project_id')
                )
                ->joinInner(
                    array('t2' => 'user_id'),
                    't1.project_id = t2.projid',
                    array('period_restriction_type')
                )
                ->where('t1.project_id = ?', $courseId);
            $select->joinLeft(
                array('t4' => $subSelect),
                't1.MID = t4.user_id',
                array('course' => 't4.project_id', 't4.user_id')
            );

        } elseif ($switcher) {
            $select->joinLeft(
                array('t2' => 'moderators'),
                't1.MID = t2.user_id',
                array()
            );
        }

        $group_fields = array('t1.MID', 't1.LastName', 't1.FirstName', 't1.Patronymic');
        if ($courseId > 0) {
            array_merge($group_fields, array('t4.period_restriction_type', 't4.user_id', 't4.project_id'));
            $group_fields = array_merge($group_fields, array('t4.project_id', 't4.period_restriction_type'));
        }
        $select->group($group_fields);

        if ($courseId > 0) {
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
                    'period_restriction_type' => array('hidden' => true),
                    'fio' => array(
                        'title' => _('ФИО'),
                        'decorator' => $this->view->cardLink(
                            $this->view->url(
                                array(
                                    'module' => 'user',
                                    'controller' => 'list',
                                    'action' => 'view',
                                    'user_id' => ''
                                )
                            ).'{{MID}}',_('Карточка пользователя')).
                            '<a href="'.$this->view->url(
                                array(
                                    'module' => 'user',
                                    'controller' => 'edit',
                                    'action' => 'card',
                                    'user_id' => ''
                                )
                            ) . '{{MID}}'.'">'.'{{fio}}</a>'),
                    'departments' => array(
                        'title' => _('Подразделение'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{departments}}', $select)
                        )
                    ),
                    'positions' => array(
                        'title' => _('Должность'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{positions}}', $select, true)
                        )
                    ),
                    'projid' => array('hidden' => true),
                    'course' => array(
                        'title' => _('Назначен на этот конкурс?'),
                        'callback' => array(
                            'function' => array($this, 'updateGroupColumn'),
                            'params' => array('{{course}}', $courseId)
                        )
                    ),
                    'tags' => array('hidden' => true),
                ),
                array(
                    'MID' => null,
                    'fio' => null,
                ),
                $this->gridId
            );
        } else {
            $grid = $this->getGrid(
                $select,
                array(
                    'MID' => array('hidden' => true),
                    'notempty' => array('hidden' => true),
                    'projects' => array(
                        'title' => _('Конкурсы'),
                        'callback' => array(
                            'function' => array($this, 'projectsCache'),
                            'params' => array('{{projects}}', $select)
                        )
                    ),
                    'fio' => array(
                        'title' => _('ФИО'),
                        'decorator' => $this->view->cardLink(
                                           $this->view->url(array(
                                                 'module' => 'user',
                                                 'controller' => 'list',
                                                 'action' => 'view',
                                                 'user_id' => '')).'{{MID}}',
                                           _('Карточка пользователя')
                                       ).
                                       '<a href="'.$this->view->url(array(
                                           'module' => 'user',
                                           'controller' => 'edit',
                                           'action' => 'card',
                                           'user_id' => ''
                                      )) . '{{MID}}'.'">'.'{{fio}}</a>',
                                                                      
                    ),
                    'departments' => array(
                        'title' => _('Подразделение'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{departments}}', $select)
                        )
                    ),
                    'positions' => array(
                        'title' => _('Должность'),
                        'callback' => array(
                            'function' => array($this, 'departmentsCache'),
                            'params' => array('{{positions}}', $select, 'pluralFormPositionsCount')
                        )
                    ),
                    'tags' => array(
                        'title' => _('Метки'),
                        'callback' => array(
                            'function'=> array($this, 'displayTags'),
                            'params'=> array('{{tags}}', $this->getService('TagRef')->getUserType())
                        )
                    ),
                ),
                array(
                    'fio' => null,
                    'tags' => array('callback' => array('function' => array($this, 'filterTags'))),
                )
            );      
        }

        if ($this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_CURATOR, HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
            if ($courseId) {
                // todo: тройной свитч?
                $grid->setGridSwitcher(array(
                    array('name' => 'moderators', 'title' => _('модераторов данного конкурса'), 'params' => array('course' => $courseId, 'all' => 0)),
                    array('name' => 'all_moderators', 'title' => _('всех модераторов конкурсов'), 'params' => array('course' => null, 'all' => 0), 'order' => 'course', 'order_dir' => 'DESC'),
                    array('name' => 'all_users', 'title' => _('всех пользователей'), 'params' => array('course' => null, 'all' => 1), 'order' => 'course', 'order_dir' => 'DESC'),
                ));
            } elseif ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_CURATOR)) {

                $grid->setGridSwitcher([
                    'label' => _('Показать всех'),
                    'title' => _('Показать всех модераторов конкурсов'),
                    'param' => self::SWITCHER_PARAM_DEFAULT,
                    'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
                ]);
            }
        }

        $grid->updateColumn('fio',
            array('callback' =>
                array('function' => array($this, 'updateFio'),
                      'params'   => array('{{fio}}', '{{MID}}')
                )
            )
        );

        $url = array('action' => 'assign');
        if ($courseId > 0) {
            $url['courseId']  = $courseId;
            $url['project_id'] = $courseId;
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
                    $assignMenuItem   = ( $courseId > 0 ) ? _('Назначить тьюторов на курс')   : _('Назначить тьюторов на курсы');
                    $unassignMenuItem = ( $courseId > 0 ) ? _('Отменить назначение тьюторов') : _('Отменить назначения тьюторов');
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
                $url['courseId']   = $courseId;
                $url['project_id'] = $courseId;
            }

            if ($unassignMenuItem) {
                $grid->addMassAction(
                    $url,
                    $unassignMenuItem,
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }
        }

        $coursesPrompt = _('Выберите конкурс');
        $collection = $this->getService('Project')->fetchAll(
            $this->getService('Project')->quoteInto('state IN (?)', array(
                HM_Project_ProjectModel::STATE_PENDING,
                HM_Project_ProjectModel::STATE_ACTUAL
            ))
        );
        $full_collection = $this->getService('Project')->fetchAll();

        if ($courseId <= 0) {
            if (count($collection)) {
                $courses = $collection->getList(
                    'projid',
                    'name',
                    $coursesPrompt
                );
            }

            if (count($full_collection)) {
                $all_courses = $full_collection->getList(
                    'projid',
                    'name',
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

        if ($courseId) $grid->setClassRowCondition("'{{course}}' != ''", "success");

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }
 
    public function assignAction()
    {
        if ($this->_serviceProject === null) $this->_serviceProject = $this->getService('Project');

        $projectId     = (int) $this->_getParam('project_id', 0);
        $gridId        = ($projectId) ? "grid{$projectId}" : 'grid';
        $postMassField = 'postMassIds_' . $gridId;
        $postMassIds   = $this->_getParam($postMassField, '');
        $courseIds     = $this->_getParam('courseId', array(0));
        
        $projectId = (int) $this->_getParam('project_id', 0);
        $courseIds = $this->_getParam('courseId', array(0));
        if (!is_array($courseIds)) $courseIds = array($courseIds);

        $gridId = ($projectId) ? "grid{$projectId}" : 'grid';

        $postMassIds = $this->_getParam('postMassIds_' . $gridId, '');

        if ((((count($courseIds) == 1)) && empty($courseIds[0])) || !strlen($postMassIds)) {
            $this->_flashMessenger->addMessage ( _ ( 'Пожалуйста выберите пользователей и укажите конкурс' ) );
        }

        if ($this->getService('Curator')->isProjectResponsibility($this->getService('User')->getCurrentUserId(), $projectId)) {

            foreach ($courseIds as $courseId) {
                $ids = explode(',', $postMassIds);
                if (count($ids)) {
                    $errors=false;
                    foreach($ids as $id) {
                        $id = (int) $id;

                        if (method_exists($this, '_preAssign')) {
                            $return = $this->_preAssign($id, $courseId);

                            if ($return === self::RETCODE_DOACTION_END_ITERATION){ // Константы кодов ошибок с описаниями находятся в начале класс
                                $errors = true;
                                continue;
                            }
                            elseif ($return === self::RETCODE_DOACTION_END_LOOP){
                                $errors = true;
                                break;
                            }
                        }

                        $fetch = $this->getService('Moderator')->fetchAll(array('user_id = ?' => $id, 'project_id = ?' => $courseId));
                        try{
                            if(count($fetch) == 0){
                                $this->_assign($id, $courseId);
                            }
                        }catch (Zend_Db_Exception  $e){
                            $errors=true;
                        }


                        if (method_exists($this, '_postAssign')) {
                            $this->_postAssign($id, $courseId);
                        }
                    }
                }
            }
        } else {
            $this->_flashMessenger->addMessage ( _ ( 'Нет прав на назначение на этот конкурс' ) );
        }

        if ($errors == false) {
            $this->_flashMessenger->addMessage ( _ ( 'Пользователи успешно назначены'));
        } else{
            $this->_flashMessenger->addMessage(_('В ходе назначения пользователей возникли ошибки'));
        }


        if (method_exists($this, '_finishAssign')) {
            $this->_finishAssign();
        }

        $messenger = $this->getService('Messenger');
        $messenger->sendAllFromChannels();

        $this->_redirector->gotoSimple('index', null, null, array('project_id' => $projectId));
        
    }

    /**
     * Функция возвращает информацию о пользователях, которых пытаются назначить слушателейм,
     * в случае, если они уже проходили обучение на каких-либо из выбранных тренингов
     * @param array $userIds - ИД пользователей для проверки
     * @param array $courseIds - ИД тренингов и сессий для проверки
     * @return array
     */
    private function _getGraduatedUsers($userIds, $courseIds)
    {
        $result    = array();
        $userIds   = (array) $userIds;
        $userIds   = array_map('intval', $userIds);
        $courseIds = (array) $courseIds;
        $courseIds = array_map('intval', $courseIds);

        $projects  = $this->_serviceProject->fetchAllDependence('Graduated', $this->quoteInto(array('projid IN (?)', ' OR base_id IN (?)'), array($courseIds, $courseIds)));

        if (!count($projects)) return $result;

        $bases     = $this->_serviceProject->fetchAllDependence('Graduated', $this->quoteInto('projid IN (?)', array_map('intval', array_unique($projects->getList('base_id')))));

        if ( count($bases) ) {
            $baseSessions = $this->_serviceProject->fetchAllDependence('Graduated', $this->quoteInto('base_id IN (?)', array_map('intval', array_unique($bases->getList('projid')))));
        } else {
            $baseSessions = $this->_serviceProject->fetchAll(array('projid = ?' => -1)); // empty collection
        }

        $projectsName = array();
        if (count($projects)) {
            $projectsName = $projects->getList('projid','name');
        }
        if (count($bases)) {
            $projectsName = $projectsName + $bases->getList('projid','name');
        }
        if (count($baseSessions)) {
            $projectsName = $projectsName + $baseSessions->getList('projid','name');
        }

        $this->_graduatedDataProcess($result, $projects, $userIds, $projectsName);
        if( count($bases) )        $this->_graduatedDataProcess($result, $bases, $userIds, $projectsName);
        if( count($baseSessions) ) $this->_graduatedDataProcess($result, $baseSessions, $userIds, $projectsName);

        return $result;
    }

    private function _graduatedDataProcess(&$result, $projects, $userIds, $projectsName)
    {
        foreach ($projects as $project) {
            if (count($project->graduated)) {
                foreach ($project->graduated as $graduated) {
                    if ( in_array($graduated->MID, $userIds) && !isset($result[$graduated->MID][$project->projid]) ) {
                        $data = array(
                            'MID'     => $graduated->MID,
                            'endDate' => $graduated->end,
                        );

                        if ($project->base_id) {
                            $data['training'] = $projectsName[$project->base_id];
                            $data['session']  = $project->name;
                        } else {
                            $data['training'] = $project->name;
                        }
                        $result[$graduated->MID][$project->projid] = $data;
                    }
                }
            }
        }
    }

    protected function _preAssign($personId, $courseId)
    {
        if(isset($this->_cacheProjectExpire[$courseId])){ // Если имеется результат в кеше
            return $this->_cacheProjectExpire[$courseId] ? self::RETCODE_DOACTION_END_ITERATION : self::RETCODE_DOACTION_OK;
        }

        $project = $this->getOne($this->_serviceProject->find($courseId));

        if (!$project) {
            return self::RETCODE_DOACTION_END_LOOP;
        }
        elseif ($project->isExpired()){
            $this->_hasErrors = true;
            $this->_cacheProjectExpire[$courseId] = true;
            $this->_expiredProjectsNames[] = $project->getName();
            return self::RETCODE_DOACTION_END_ITERATION;
        }

        $this->_cacheProjectExpire[$courseId] = false;
        return self::RETCODE_DOACTION_OK;
    }

    protected function _postAssign($personId, $courseId)
    {
        return true; //#10357
    }

    protected function _finishAssign()
    {
        if ($this->_hasErrors){
            $this->_flashMessenger->clearCurrentMessages();
            $this->_flashMessenger->addMessage(array(
                'type'        => HM_Notification_NotificationModel::TYPE_ERROR,
                'message'    => _('Срок действия следующих конкурсов истёк: '.implode(', ', $this->_expiredProjectsNames))
            ));
        }
    }

    protected function _assign($personId, $projectId)
    {
        return $this->getService('Project')->assignModerator($projectId, $personId);
    }

    protected function _unassign($personId, $projectId)
    {
        return $this->getService('Project')->unassignModerator($projectId, $personId);
    }

    protected function _preUnassign($personId, $courseId){}
    protected function _postUnassign($personId, $courseId){}
    protected function _finishUnassign(){}

    public function unassignAction(){}
}