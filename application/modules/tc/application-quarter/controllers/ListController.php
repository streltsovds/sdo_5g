<?php
/**
 * Created by PhpStorm.
 * User: cuthuk
 * Date: 13.10.2014
 * Time: 7:30
 */


class ApplicationQuarter_ListController extends HM_Controller_Action {

    /** @var HM_Tc_Application_ApplicationService $_defaultService */
    protected $_defaultService;

    protected $_sessionQuarter;
    protected $_department;
    protected $_sessionDepartment;

    public function init()
    {
        /** @var  $this->_defaultService HM_Tc_Application_ApplicationService */
        $this->_defaultService = $this->getService('TcApplication');

        // аккордеон
        $sessionQuarterId = $this->_getParam('session_quarter_id', 0);
        $this->_sessionQuarter      = $this->getOne(
            $this->getService('TcSessionQuarter')->fetchAllDependence(
                array('Cycle', 'Department'),
                $this->quoteInto('session_quarter_id = ?', $sessionQuarterId))
        );

        if ($this->_sessionQuarter) {
            $this->view->setExtended(
                array(
                    'subjectName' => 'TcSessionQuarter',
                    'subjectId' => $sessionQuarterId,
                    'subjectIdParamName' => 'session_quarter_id',
                    'subjectIdFieldName' => 'session_quarter_id',
                    'subject' => $this->_sessionQuarter
                )
            );
        }

        $currentRole = $this->getService('User')->getCurrentUserRole();
        if (
            $this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))
        ) {
            // менеджер видит только в режиме консолидированной заявки
            $this->_department = $this->getService('Orgstructure')->getResponsibleDepartment();
            if ($this->_sessionQuarter && $this->_department) {
                $this->_sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($this->_sessionQuarter, $this->_department->soid);
            }

        } elseif ($sessionDepartmentId = $this->_getParam('session_department_id', 0)) {

            // если передан session_department_id, значит грид в режиме консолидированной заявки
            $this->_sessionDepartment = $this->getOne($this->getService('TcSessionDepartment')->find($sessionDepartmentId));
        }

        parent::init();
    }

    public function indexAction()
    {
        $currentUserRole = $this->getService('User')->getCurrentUserRole();
        
        // фильтры для запроса
        $options = array(
            'sessionQuarterId' => $this->_sessionQuarter->session_quarter_id,
            // непосредственное подразделение (не уровень конс.заявки)
            'departmentId' => $this->_department ? $this->_department->soid : 0,
            // подразделение уровеня конс.заявки
            'sessionDepartmentId' => $this->_sessionDepartment ? $this->_sessionDepartment->session_department_id : 0,
            'showButton' => false
        );
        
        if ($this->getService('Acl')->inheritsRole($currentUserRole, array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            // Находим должность супервайзера
            $position = $this->getService('Orgstructure')->fetchAll(array('mid =?' => $this->_user->MID))->current();
            // Находим подразделение, в котором начальствует этот супервайзер
            $parent = $this->getService('Orgstructure')->fetchAll(array('soid =?' => $position->owner_soid))->current();
            
            $options['departmentId'] = array($parent->soid, $parent->owner_soid);
            $descendants = $this->getService('Orgstructure')->getDescendants(
                    $parent->soid,
                    false, 
                    HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
            $options['departmentId'] = array_merge($options['departmentId'], $descendants);
            unset($options['sessionDepartmentId']);
        }

        $listSource = $this->_defaultService->getClaimantQuarterListSource($options, false, false);
//        exit($listSource->__toString());

        // если смотрим в режиме консолидированной заявки
        if ($this->_sessionDepartment) {

            // если смотрим в режиме консолидированной заявки - над гридом сводка
            $stmt = $listSource->query();
            $res = $stmt->fetchAll();
            $fact = $req = 0;
            $additionalCounter  = 0;

            foreach($res as $val){
                if ($val['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                    $fact += $val['price'];
                    $additionalCounter++;
                } else {
                    $req += $val['price'];
                }
            }
            $cost = array(
                _('Планируетмые затраты на обязательное обучение') => number_format($req, 0, '.', ' '),
                _('Планируетмые затраты на рекомендованное и инициативное обучение') => number_format($fact, 0, '.', ' '),
                _('Выбрано курсов инициативного обучения') => $additionalCounter
            );

            $departmentChiefs = $this->getService('Orgstructure')->fetchAll(
                $this->getService('Orgstructure')->quoteInto(
                    array(' owner_soid = ? AND type = 1 AND is_manager = 1'),
                    array($this->_sessionDepartment->department_id)
                )
            )->getList('mid');

            $currentUser = $this->getService('User')->getCurrentUserId();

            if (in_array($currentUser, $departmentChiefs)) {
                $options['showButton'] = true;
                $options['sessionDepartmentId'] = $this->_sessionDepartment ? $this->_sessionDepartment->session_department_id : 0;
            }
        }

        // собственно, грид
        $grid = HM_ApplicationQuarter_Grid_ApplicationQuarterGrid::create(
            array_merge(array('controller' => $this), $options)
        );
        
        $this->view->assign(array(
            'isGridAjaxRequest' => $this->isGridAjaxRequest(),
            'session'           => $this->_sessionQuarter,
            'grid'              => $grid->init($listSource),
            'cost'              => $cost
        ));
    }

    protected function _goToIndex()
    {
        $request = $this->getRequest();
        $this->_redirector->gotoUrl($request->getHeader('referer'));
    }

    /*
     * На эту страницу приходят из следующих мест:
     * * со страницы "все персональные заявки"
     * * со страницы "инициативное обучение" над гридом
     */
    public function createAction()
    {
        $this->view->setHeader(_('Создание заявки'));
        $categoryParam = $this->getRequest()->getParam('category');

        $subjectId = $this->_getParam('subid');
        if ($subjectId) {
            $subject = $this->getService('Subject')->getOne(
                $this->getService('Subject')->find($subjectId)
            );
            if ($subject) {
                $this->view->setSubHeader($subject->name);
            }
        }

        $sessionId = $this->getRequest()->getParam('session_quarter_id');
        $session   = $this->getService('TcSessionQuarter')->getOne(
            $this->getService('TcSessionQuarter')->find($sessionId)
        );
        $cycle     = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_ApplicationQuarter();

        // только месяцы, относящиеся к периоду квартального планирования
        $form->getElement('period')->setMultiOptions($tcApplicationService->getSessionPeriodsForForm($this->_sessionQuarter));

        // статьями занимается только менеджер
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $form->removeElement('cost_item');
        } else {
            // только 2 статьи затрат, по которым реально учатся (не конкурс)
            $form->getElement('cost_item')->setMultiOptions($tcApplicationService->getCostItemsForForm());
        }

        $form->removeElement('user_id');
        // вообще все пользователи, либо только подчиненные
        $form->getElement('users')->setAttrib('multiOptions', $tcApplicationService->getUsersForForm($this->_sessionQuarter));

        $form->getElement('category')->setAttribs(array(
            'onchange' => "getInitialCoursesList();if(this.value == 1) { $('fieldset#fieldset-typegroup').hide(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.");} else { $('fieldset#fieldset-typegroup').show(); addPeriodOptions(false,".$cycle->year.",".$sessionId.",".$applicationId."); }",
        ));
        // create - это всегда инициативное
        $form->getElement('category')->setAttrib('value', HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION);

        if ($subject) {
            // конкретный курс, если пришли по прямой ссылке
            $form->removeElement('subject_id');
            $form->addElement('hidden', 'subject_id', array('value' => $subject->subid));

        } else {
            // все курсы, сгруппированные по провайдерам
            $form->getElement('subject_id')->setMultiOptions(
                $tcApplicationService->getSubjectsForForm($categoryParam)
            );
        }


        $post = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($post)) {

                $positionUsers = $this->_getPositionUsers($post['users']);
                $applicationResult = array(
                    'success' => array(),
                    'success_empty' => 0,
                    'dublicate' => array(),
                    'fulldp' => array(),
                    'fulldp_empty' => array(),
                );

                if (!$subject && $post['subject_id']) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                }

                foreach ($positionUsers as $userId => $positionData) {

                    $departmentId = $positionData['department'];
                    $sessionDepartment = $this->getService('TcSessionDepartment')->getSessionDepartmentByDescendant($this->_sessionQuarter, $departmentId);

                    $insert = array(
                        'session_quarter_id' => $this->_sessionQuarter->session_quarter_id,
                        'department_id' => $departmentId,
                        'session_department_id' => $sessionDepartment->session_department_id,
                        'user_id' => $userId,
                        'position_id' => $positionData['soid'],
                        'criterion_id' => $subject ? $subject->criterion_id : null,
                        'criterion_type' => $subject ? $subject->criterion_type : null,
                        'subject_id' => $subject ? $subject->subid : $post['subject_id'],
                        'provider_id' => $subject ? $subject->provider_id : null,
                        'period' => $post['period'],
                        'category' => $post['category'], // HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION,
                        'created' => date('Y-m-d'),
                        'status' => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                        'cost_item' => $post['cost_item'],
                        'event_name' => $post['event_name'],
                        'initiator' => $this->getService('User')->getCurrentUserId()
                    );

                    if ($post['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                        $insert['payment_type'] = $post['payment_type'];
                        switch ($post['payment_type']) {
                            case HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY:
                                $insert['price_employee'] = 0;
                                $insert['price'] = $subject ? $subject->price : 0;
                                break;
                            case HM_Tc_Application_ApplicationModel::PAYMENT_EMPLOYEE:
                                $insert['price_employee'] = $subject ? $subject->price : 0;
                                $insert['price'] = 0;
                                break;
                            case HM_Tc_Application_ApplicationModel::PAYMENT_PARTIAL:
                                $price = $subject ? $subject->price : 0;
                                $partialPrice = (int) round($price * $post['payment_percent'] * 0.01);
                                $insert['price_employee'] = $partialPrice;
                                $insert['price'] = $price - $partialPrice;
                                $insert['payment_percent'] = $post['payment_percent'];
                                break;
                        }
                    } else {
                        $insert['payment_type'] = HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY;
                        $insert['price_employee'] = 0;
                        $insert['price'] = $subject ? $subject->price : 0;
                        $insert['payment_percent'] = 0;
                    }

                    $tcApplicationService->insert($insert);
                }

                if ($applicationResult['success'] || $applicationResult['success_empty']) {
                    $total = count($applicationResult['success']) + $applicationResult['success_empty'];
                    $plural = sprintf(_n('заявка plural', '%s заявка', $total), $total);
                    $this->_flashMessenger->addMessage(
                        ($total == 1 ? _('Успешно подана ') : _('Успешно поданы ')) . $plural .
                        ($applicationResult['success']
                            ? ((count($applicationResult['success']) == 1
                                    ? _(' для пользователя ')
                                    : _(' для пользователей '))
                                . implode(', ', $applicationResult['success']))
                            : ''
                        )
                    );
                }

                $this->_redirect($this->view->url(
                    array(
                        'baseUrl' => false,
                        'module' => 'application-quarter',
                        'controller' => 'list',
                        'action' => 'index',
                        'session_quarter_id' => $this->_sessionQuarter->session_quarter_id,
                    )));

            }
        }

        $this->view->form = $form;
    }

    /*
     * На эту страницу приходят из следующих мест:
     * * со страницы "все персональные заявки" (все типы)
     * * со страницы "обязательное обучение"
     */
    public function editAction()
    {
        $this->view->setHeader(_('Редактирование заявки'));

        $applicationId = $this->_getParam('application_id');
        $application   = $this->getService('TcApplication')->getOne(
            $this->getService('TcApplication')->findDependence('Users', $applicationId)
        );
        if ($application && $application->user) {
            $user = $application->user->current();
            $this->view->setSubHeader($user->getName());
        }

        $sessionId = $this->getRequest()->getParam('session_quarter_id');
        $session   = $this->getService('TcSessionQuarter')->getOne(
            $this->getService('TcSessionQuarter')->find($sessionId)
        );
        $cycle     = $this->getService('Cycle')->getOne(
            $this->getService('Cycle')->find($session->cycle_id)
        );

        /** @var HM_Tc_Application_ApplicationService $tcApplicationService */
        $tcApplicationService = $this->getService('TcApplication');
        $form = new HM_Form_ApplicationQuarter();

        // только месяцы, относящиеся к периоду квартального планирования
        $sessionPeriods = $tcApplicationService->getSessionPeriodsForForm($this->_sessionQuarter, false);
        $form->getElement('period')->setMultiOptions($sessionPeriods);
        $form->getElement('period')->setValue($application->period);

        // статьями занимается только менеджер
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $form->removeElement('cost_item');
        } else {
            // только 2 статьи затрат, по которым реально учатся (не конкурс)
            $form->getElement('cost_item')->setMultiOptions($tcApplicationService->getCostItemsForForm());
        }

        // только курсы, сгруппированные по провайдерам
        $form->getElement('subject_id')->setMultiOptions($tcApplicationService->getSubjectsForForm($application->category));

        $form->removeElement('users');
        if (!$application->user_id) {
            $form->getElement('user_id')->setMultiOptions($tcApplicationService->getDepartmentUsersForForm($application));
        } else {
            $form->removeElement('user_id');
            $form->addElement('hidden', 'user_id', array('value' => $application->user_id));
        }

        $form->addElement('hidden', 'application_id', array('value' => $application->application_id));

        $form->getElement('category')->setAttribs(array(
            'onchange' => "getInitialCoursesList();if(this.value == 1) { $('fieldset#fieldset-typegroup').hide(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.", 'application-quarter');} else { $('fieldset#fieldset-typegroup').show(); addPeriodOptions(true,".$cycle->year.",".$sessionId.",".$applicationId.", 'application-quarter'); }",
        ));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();

            // чтобы пройти валидацию
            $form->getElement('subject_id')->setMultiOptions($tcApplicationService->getSubjectsForForm($post['category']));

            if ($form->isValid($post)) {

                // курс мог измениться; этом случае надо изменить и провайдера
                if ($post['subject_id'] && ($post['subject_id'] != $application->subject_id)) {
                    $subject = $this->getService('Subject')->getOne(
                        $this->getService('Subject')->find($post['subject_id'])
                    );
                }

                $data = array(
                    'application_id'  => $applicationId,
                    'period'          => $post['period'],
                    'subject_id'      => $subject ? $subject->subid : $application->subject_id,
                    'provider_id'     => $subject ? $subject->provider_id : $application->provider_id,
                    'status'          => HM_Tc_Application_ApplicationModel::STATUS_ACTIVE,
                    'category'        => $post['category'],
                    'user_id'         => $post['user_id'],
                    'cost_item'       => $post['cost_item'],
                );

                if ($post['category'] != HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED) {
                    $data['payment_type'] = $post['payment_type'];
                    switch ($post['payment_type']) {
                        case HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY:
                            $data['price_employee'] = 0;
                            $data['price'] = $subject ? $subject->price : $application->price;
                            break;
                        case HM_Tc_Application_ApplicationModel::PAYMENT_EMPLOYEE:
                            $data['price_employee'] = $subject ? $subject->price : $application->price;
                            $data['price'] = 0;
                            break;
                        case HM_Tc_Application_ApplicationModel::PAYMENT_PARTIAL:
                            $price = $subject ? $subject->price : $application->price;
                            $partialPrice = (int) round($price * $post['payment_percent'] * 0.01);
                            $data['price_employee'] = $partialPrice;
                            $data['price'] = $price - $partialPrice;
                            $data['payment_percent'] = $post['payment_percent'];
                            break;
                    }
                } else {
                    $data['payment_type'] = HM_Tc_Application_ApplicationModel::PAYMENT_COMPANY;
                    $data['price_employee'] = 0;
                    $data['price'] = $subject ? $subject->price : $application->price;
                    $data['payment_percent'] = 0;
                }

                $student = $this->getService('TcApplication')->fetchAll(array(
                    'subject_id = ?' => $post['subject_id'],
                    'user_id = ?' => $post['user_id'],
                    'study_status != ?' => HM_Tc_Application_ApplicationModel::STUDY_STATUS_COMPLETE
                ));

                if (count($student) && $form->getElement('user_id')->getType() != 'Zend_Form_Element_Hidden') {
                    $this->_flashMessenger->addMessage(_('Пользователь уже назначен на курс'));

                    $this->_redirect($this->view->url(
                        array(
                            'baseUrl' => false,
                            'module' => 'application-quarter',
                            'controller' => 'list',
                            'action' => 'index',
                            'session_quarter_id' => $this->_sessionQuarter->session_quarter_id,
                            'application_id' => null,
                        )));
                } else {
                    $tcApplicationService->update($data);

                    $this->_flashMessenger->addMessage(_('Заявка успешно отредактирована'));

                    $this->_redirect($this->view->url(
                        array(
                            'baseUrl' => false,
                            'module' => 'application-quarter',
                            'controller' => 'list',
                            'action' => 'index',
                            'session_quarter_id' => $this->_sessionQuarter->session_quarter_id,
                            'application_id' => null,
                        ))
                    );
                }
            }
        } else {
            $data = array(
                'subject_id'      => $application->subject_id,
                'cost_item'       => $application->cost_item,
                'period'          => $application->period,
                'category'        => $application->category,
                'payment_type'    => $application->payment_type,
                'payment_percent' => $application->payment_percent,
                'user_id'         => $application->user_id
            );
            $form->populate($data);
        }
        $this->view->form = $form;
    }

    public function getPeriodsAction()
    {

        $applicationId = $this->_getParam('application_id');
        $application   = $this->getService('TcApplication')->getOne(
            $this->getService('TcApplication')->findDependence('Users', $applicationId)
        );

        $category = $this->_getParam('category');
        $session = $this->_session;
        if ($this->_request->getModuleName() == 'application-quarter') {
            $sessionId = $this->_request->getParam('session_id');
            $session = $this->getService('TcSessionQuarter')->findDependence('Cycle', $sessionId)->current();
        }
        $app = ($category == 2) ? false : $application;
        $sessionPeriods = $this->getService('TcApplication')->getSessionPeriodsForForm($session, $app);

        $options = '';
        foreach ($sessionPeriods as $key => $period) {
            $options .= '<option value="'.$key.'">'.$period.'</option>';
        }

        echo $options;
        exit();
    }

    public function deleteAction()
    {
        $applicationId = $this->_getParam('application_id', 0);
        if ($applicationId) {
            $result = $this->_defaultService->delete($applicationId);
        }
        if ($result) {
            $this->_flashMessenger->addMessage(_('Персональная заявка успешно удалена'));
        } else {
            $this->_flashMessenger->addMessage(_('При удалении персональной заявки произошли ошибки'));
        }
        $this->_goToIndex();
    }

    public function deleteByAction()
    {
        $grid = $this->_request->getParam('grid');
        $applicationIds = $this->_request->getParam('postMassIds_' . $grid);
        $applicationIds = explode(',', $applicationIds);

        if (is_array($applicationIds) && count($applicationIds)) {
            $count = 0;
            foreach ($applicationIds as $applicationId) {
                $result = $this->_defaultService->delete($applicationId);
                $count = ($result) ? $count + 1 : $count;
            }
        }
        $this->_flashMessenger->addMessage(sprintf(
            _('Удалено - %d, невозможно удалить - %d'),
            $count, count($applicationIds) - $count)
        );
        $this->_goToIndex();
    }

    public function getSessionQuarter()
    {
        return $this->_sessionQuarter;
    }


    protected function _getPositionUsers($userIds)
    {
        $select = $this->getService('Orgstructure')->getSelect();
        $select->from(array('so' => 'structure_of_organ'),
            array(
                'user_id' => 'so.mid',
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'soid' => 'so.soid',
                'department' => new Zend_Db_Expr('CASE WHEN so.is_manager=0 THEN so.owner_soid ELSE so2.owner_soid END')
            ))
            ->joinInner(array('so2' => 'structure_of_organ'),
                'so2.soid=so.owner_soid',
                array()
            )
            ->joinInner(array('p' => 'People'),
                'so.mid=p.MID',
                array()
            )
            ->where('so.mid in (?)  and so.blocked=0', array($userIds));

        $result = $select->query()->fetchAll();
        $positionUsers = array();
        foreach ($result as $row) {
            $positionUsers[$row['user_id']] = $row;
        }

        return $positionUsers;
    }
}