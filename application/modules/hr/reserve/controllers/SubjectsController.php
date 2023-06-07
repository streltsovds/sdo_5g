<?php
class Reserve_SubjectsController extends HM_Controller_Action_Reserve
{
    use HM_Controller_Action_Trait_Grid;

    // Сообщения

    const MSG_COURSE_NOT_FOUND = 'Следующие курсы не были найдены в базе: %s';
    const MSG_COURSE_SUCCESS_ASSIGNED = 'Следующие курсы были успешно назначены: %s';
    const MSG_SOMEBODY_ALREADY_ASSIGNED = 'Следующие курсы уже были назначены этому слушателю: %s';
    const MSG_SOME_COURSE_EXPIRED = 'Срок действия следующих курсов истёк: %s';
    const MSG_SOME_COURSE_FULLTIME = 'Следующие курсы могут быть назначены только путём включения в годовой план обучения: %s';

    /**
     * Экшн для списка курсов
     */
    public function indexAction()
    {

        $userId = $this->_reserve->user_id;

        $order = $this->_getParam('ordergrid');
        if($order == ''){
            // @todo: есть подозрение, что в Orcale оно работает наоборот
            $this->_setParam('ordergrid', 'status_DESC');
        }

        $select = $this->getService('Subject')->getSelect();
        $subSelect = clone $select;
        $subSelectTc = clone $select;
        $subSelectClaimants = clone $select;

        $subSelect->from(array('Students'), array('MID', 'CID'))->where('MID = ?', $userId);

        // строго говоря, этого недостаточно;
        // надо еще смотреть статус сессии, даты сессии, период сессии vs период КР и т.п.
        $subSelectTc
            ->from(array('tc_applications'), array('user_id', 'subject_id', 'status'))
            ->where('user_id = ?', $userId);

        $subSelectClaimants
            ->from(array('claimants'), array('MID', 'CID'))
            ->where('status = ?', HM_Role_ClaimantModel::STATUS_NEW)
            ->where('MID = ?', $userId);

        $select->from(array('s' => 'subjects'), array(
            'subid' => 's.subid',
            'name' => 's.name',
            'tcprovider'   => 'pr.provider_id',
            'provider' => 'pr.name',
            'price' => 's.price',
            'status' => 'd.MID',
            'claimant_status' => 'c.MID',
            'tc_status' => new Zend_Db_Expr("CASE WHEN a.status IS NULL THEN 3 ELSE a.status END"),
        ))
            ->joinLeft(array('d' => $subSelect),
                's.subid = d.CID',
                array()
            )
            ->joinLeft(array('c' => $subSelectClaimants),
                's.subid = c.CID',
                array()
            )
            ->joinLeft(array('a' => $subSelectTc),
                's.subid = a.subject_id',
                array()
            )
            ->joinLeft(array('pr' => 'tc_providers'), 'pr.provider_id = s.provider_id', array())
            ->where('s.is_labor_safety != ?', 1)
            ->where('((s.category IS NULL) OR (s.category != ?))', HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY)
            ->group(array(
                's.subid',
                's.name',
                'pr.provider_id',
                'pr.name',
                's.price',
                'd.MID',
                'c.MID',
                'a.status',
            ));

        $isManager = $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL));


        //Область ответственности
        if($this->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_DEAN){
            $select = $this->getService('Responsibility')->checkSubjects($select, 's.subid');
        }

        $urlProvider = array('baseUrl' => 'tc', 'module' => 'provider', 'controller' => 'list', 'action' => 'view', 'provider_id' => '{{tcprovider}}');
        $grid = $this->getGrid($select,
            array(
                'subid' => array('hidden' => true),
                'tcprovider' => array('hidden' => true),
                'claimant_status' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' =>
                        $this->view->cardLink($this->view->url(array('baseUrl' => '', 'module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}', _('Карточка учебного курса')) .
                        ($isManager
                            ? '<a href="'.$this->view->url(array('baseUrl' => '', 'module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'subject_id' => ''), null, true) . '{{subid}}'.'">'. ' {{name}}</a>'
                            : '{{name}}'
                        )
                ),
                'provider'      => array(
                    'title' => _('Провайдер'),
                    'decorator' => $isManager
                        ? $this->view->cardLink($this->view->url(array('baseUrl' => 'tc', 'module' => 'provider', 'controller' => 'list','action' => 'card', 'provider_id' => '')) . '{{tcprovider}}', _('Карточка провайдера')) . ' <a href="' . $this->view->url($urlProvider, null, true, false) . '">{{provider}}</a>'
                        : '{{provider}}',
                ),
                'price' => array('title' => _('Стоимость, руб.')),
                'status' => array('title' => _('Назначен')),
                'tc_status' => array('title' => _('Включен в годовой план обучения?'))
            ),
            array(
                'name' => null,
                'provider' => null,
                'price' => null,
                'status' => array('values' => array( $userId => _('Да'), 'ISNULL' => _('Нет'),)),
                'tc_status' => array(
                    'values' => array(
                            HM_Tc_Application_ApplicationModel::STATUS_INACTIVE => _('Не согласовано'),
                            HM_Tc_Application_ApplicationModel::STATUS_ACTIVE => _('Заявка'),
                            HM_Tc_Application_ApplicationModel::STATUS_COMPLETE => _('Согласовано'),
                            3 => _('Нет'),
                        )
                    ),
            )
        );

        //$grid->addMassAction(array('action' => 'index'), _('Выберите действие'));

        $sessions = $this->getService('TcSession')
            ->fetchAll(array('status = ?' => HM_Tc_Session_SessionModel::GOING), 'date_begin')
            ->getList('session_id', 'name');

        $grid->addMassAction(array('action' => 'apply'), _('Включить заявку в годовой план обучения'), _('Вы действительно желаете включить данный курс в годовой план обучения?'));
        $grid->addSubMassActionSelect($this->view->url(array('action' => 'apply')),
            'session_id',
            $sessions);

//        if ($this->_reserve->state_id == HM_Hr_Reserve_ReserveModel::PROCESS_STATE_OPEN) {
        if (!$isManager) {
            $grid->addMassAction(array('action' => 'assign-responsibilities'), _('Подать заявку на обучение'), _('Вы действительно желаете подать заявку на обучение по данным курсам? В зависимости от типа курса она может рассматриваться менеджером по обучению либо быть автоматически одобрена. Продолжить?'));
        } else {
            $grid->addMassAction(array('action' => 'assign-responsibilities'), _('Назначить на обучение'), _('Вы действительно желаете назначить участника КР на данные курсы?'));
            $grid->addMassAction(array('action' => 'delete'), _('Отменить назначение курсов'), _('Вы действительно желате отменить назначение на выбранные курсы?'));
        }

        $grid->updateColumn('status',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateStatus'),
                        'params' => array('{{status}}', '{{claimant_status}}')
                    )
            )
        );

        $grid->updateColumn('tc_status',
            array(
                'callback' =>
                    array(
                        'function' => array($this, 'updateTcStatus'),
                        'params' => array('{{tc_status}}')
                    )
            )
        );

        if ($userId) $grid->setClassRowCondition("'{{status}}' != ''", "success");

//        $grid->addFixedRows($this->_getParam('module'), $this->_getParam('controller'),$this->_getParam('action'), 'subid');
        $grid->updateColumn('fixType', array('hidden' => true));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }


    /**
     * Экшн для присваивания ответственностей
     */
    public function assignResponsibilitiesAction()
    {
        $userId = $this->_reserve->user_id;
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $studentService	= $this->getService('Student');
        $subjectService	= $this->getService('Subject');
        $userService	= $this->getService('User');

        $messages = array(
            'not_found'	=> array(),
            // 'already'	=> array(),
            'expired'	=> array(),
            'fulltime'	=> array(),
            'success'	=> array(),
        );

        foreach ($ids as $value){
            $subject = $this->getOne($subjectService->find($value));

            if(!$subject){ // Курс не найден
                $messages['not_found'][] = $subject->getName();
                continue;
            }

            if($subject->isExpired()) {  // Истёк срок действия курса
                $messages['expired'][] = $subject->getName();
                continue;
            }

            if (($subject->type == HM_Subject_SubjectModel::TYPE_FULLTIME) && $subject->price) {  // Истёк срок действия курса
                $messages['fulltime'][] = $subject->getName();
                continue;
            }

            if($studentService->isUserExists($value, $userId)){ // Пользователь уже назначен на этот курс
                // $messages['already'][] = $subject->getName();
                continue;
            }

            if($this->getService('Dean')->isSubjectResponsibility($userService->getCurrentUserId(), $value)){
                $subjectService->assignUser($value, $userId);
                $messages['success'][] = $subject->getName();
            }
        }

        if(!empty($messages['not_found'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => sprintf(_(self::MSG_COURSE_NOT_FOUND), implode(', ', $messages['not_found']))
        ));

        /*if(!empty($messages['already'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
             'message' => sprintf(_(self::MSG_SOMEBODY_ALREADY_ASSIGNED), implode(', ', $messages['already']))
        ));*/

        if(!empty($messages['expired'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => sprintf(_(self::MSG_SOME_COURSE_EXPIRED), implode(', ', $messages['expired']))
        ));

        if(!empty($messages['fulltime'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => sprintf(_(self::MSG_SOME_COURSE_FULLTIME), implode(', ', $messages['fulltime']))
        ));

        if(!empty($messages['success'])) $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => sprintf(_(self::MSG_COURSE_SUCCESS_ASSIGNED), implode(', ', $messages['success']))
        ));

        $this->_redirectToIndex();
    }

    public function applyAction()
    {
        $userId = $this->_reserve->user_id;
        $subjectIds = explode(',', $this->_request->getParam('postMassIds_grid'));
        $sessionId    = $this->_getParam('session_id', 0);

        $user = $this->getOne(
            $this->getService('User')->findDependence(
                array('Position'),
                $userId
        ));
        $position = count($user->positions) ? $user->positions->current() : false;

        $session = $this->getOne(
            $this->getService('TcSession')->findDependence(
                array('Cycle', 'Department'),
                $sessionId
        ));

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');

        $existingApplications = $tcApplicationService->fetchAll(array(
            'user_id = ?' => $userId,
            'session_id = ?' => $sessionId,
        ));
        $existingSubjectIds = count($existingApplications) ? $existingApplications->getList('subject_id') : array();
        $subjectIds = array_diff($subjectIds, $existingSubjectIds);

        $subjects = $this->getService('Subject')->fetchAll(array('subid IN (?)' => $subjectIds))->asArrayOfObjects();

        if (!$user || !$position || !$session) {

            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => sprintf(_('Произошла ошибка при включении заявки в сессию планирования'))
            ));
            $this->_redirectToIndex();
        }

        $periods = $tcApplicationService->getSessionPeriodsForForm($session);
        $period = array_shift(array_keys($periods));

        $departmentId = $position->owner_soid;
        $sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($session, $departmentId);

        $data = array(
            'session_id' => $session->session_id,
            'department_id' => $departmentId,
            'session_department_id' => $sessionDepartment->session_department_id,
            'user_id' => $userId,
            'position_id' => $position->soid,
            'period' => $period,
            'category' => HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION,
            'created' => date('Y-m-d'),
            'status' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
            'initiator' => $this->getService('User')->getCurrentUserId(),
            'payment_type' => HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY,
            'price_employee' => 0,
            'payment_percent' => 0,
        );

        foreach ($subjects as $subject) {

            $dataSubject = array(
                'criterion_id' => $subject->criterion_id,
                'criterion_type' => $subject->criterion_type,
                'subject_id' => $subject->subid,
                'provider_id' => $subject->provider_id,
                'price' => $subject->price,
            );

            $tcApplicationService->insert(array_merge($data, $dataSubject));
        }

        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => sprintf(_('Заявки успешно включены в сессию планирования'))
        ));
        $this->_redirectToIndex();
    }

    /**
     * Экшн для удаления ответственностей
     */
    public function deleteAction()
    {
        $userId = $this->_getParam('user_id', 0);
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $service = $this->getService('Student');
        // Флаг, есть ли ошибки
        $error = false;
        foreach ($ids as $value) {
            if($this->getService('Dean')->isSubjectResponsibility($this->getService('User')->getCurrentUserId(), $value)){
                $res = $service->deleteBy(
                    array(
                        'MID = ?' => $userId,
                        'CID = ?' => $value
                    )
                );
            }
        }

        if ($error === true) {
            $this->_flashMessenger->addMessage(_('На некоторых курсах пользователь не был слушателем'));
        } else {
            $this->_flashMessenger->addMessage(_('Курсы успешно удалены'));
        }
        $this->_redirector->gotoSimple('assign', 'student', 'user', array('user_id' => $userId));

    }

    /**
     * @param string $field Поле из таблицы
     * @return string Возвращаем статус
     */
    public function updateStatus($status, $claimantStatus)
    {
        if ($claimantStatus) {
            return _('Заявка');
        } elseif ($status) {
            return _('Да');
        } else {
            return _('Нет');
        }
    }

    public function updateTcStatus($tcStatus)
    {
        switch ($tcStatus) {
            case HM_Tc_Application_ApplicationModel::STATUS_INACTIVE:
                return _('Не согласовано');
            case HM_Tc_Application_ApplicationModel::STATUS_ACTIVE:
                return _('Заявка');
            case HM_Tc_Application_ApplicationModel::STATUS_COMPLETE:
                return _('Согласовано');
            default:
                return _('Нет');
        }
    }

    public function updateName($name, $subjectId) {

        return '<a href="' .
            $this->view->url(
                array('module' => 'subject',
                    'controller' => 'index',
                    'action' => 'index',
                    'subject_id' => $subjectId
                )
            ) .
            '">' . $name . '</a>';


    }

    protected function _redirectToIndex()
    {
        $url = $this->view->url(array('module' => 'reserve', 'controller' => 'subjects', 'action' => 'index', 'baseUrl' => 'hr', 'reserve_id' => $this->_reserve->reserve_id));
        $this->_redirector->gotoUrl($url, array('prependBase' => false));
    }

}