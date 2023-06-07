<?php
class Session_ReportController extends HM_Controller_Action_Session
{
    use HM_Controller_Action_Trait_Report;
    use HM_Controller_Action_Trait_Session_Report;

    public function init()
    {
        $this->initReport();
        parent::init();
        if ($backUrl = $this->_getParam('redirect')) {
            $this->view->setBackUrl(urldecode($backUrl));
        }
    }

    public function cardAction()
    {
	if ($this->_session) {
        $beginDate = new HM_Date($this->_session->begin_date);
        $endDate = new HM_Date($this->_session->end_date);
        $this->view->lists['generalLeft'] = array(
            _('Дата начала') => $beginDate->get(HM_Date::DATE_MEDIUM),
            _('Дата окончания') => $endDate->get(HM_Date::DATE_MEDIUM),
            _('Статус') => HM_At_Session_SessionModel::getStateTitle($this->_session->state),
        );
	}
    }

    public function indexAction()
    {
        $this->view->setHeader(_('Отчет о результатах оценочной сессии'));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))){
            $department = $this->getService('Orgstructure')->getDefaultParent();
            if ($department) {
                if (count($collection = $this->getService('Orgstructure')->fetchAll(array(
                    'lft > ?' => $department->lft,
                    'rgt < ?' => $department->rgt,
                    'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                )))) {
                    $slaves = $collection->getList('mid');
                }
            }

            $where = $this->quoteInto(array('session_id = ?',' AND user_id IN (?)'),array($this->_session->session_id, $slaves));
            $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence(array('CriterionValue', 'User'), array($where));

        } else {
            $sessionUsers = $this->getService('AtSessionUser')->fetchAllDependence(array('CriterionValue', 'User'), array('session_id = ?' => $this->_session->session_id));
        }
        $sessionEvents = $this->getService('AtSessionEvent')->fetchAllDependence(array(), array('session_id = ?' => $this->_session->session_id));
        $cycle = $this->_session->cycle ? $this->_session->cycle->current()->name : '';

        $sessionBeginDate = new HM_Date($this->_session->begin_date);
        $sessionEndDate = new HM_Date($this->_session->end_date);
        $this->view->lists['session'] = array(
            _('Оценочная сессия') => $this->_session->name,
            _('Оценочный период') => $cycle,
            _('Даты проведения оценки') => sprintf(_('c %s по %s'), $sessionBeginDate->toString('dd.MM.yyyy'), $sessionEndDate->toString('dd.MM.yyyy')),
            _('Дата подготовки отчета') => date('d.m.Y'),
        );

        $sessionEventsCompleted = 0;
        foreach ($sessionEvents as $event) {
            if ($event->status == HM_At_Session_Event_EventModel::STATUS_COMPLETED) $sessionEventsCompleted++;
        }
        $this->view->lists['stats'] = array(
            _('Количество участников') => count($sessionUsers),
            _('Количество анкет') => count($sessionEvents),
            _('Процент заполнения анкет') => count($sessionEvents) ? round(100 * $sessionEventsCompleted / count($sessionEvents)) . '%' : '',
        );

        $profileIds = $profiles = array();
        $profileIds = $sessionUsers->getList('profile_id');
        $profileSesionUsers = $sessionUsersCache = $usersCache = array();
        foreach ($sessionUsers as $sessionUser) {
            $profileSesionUsers[$sessionUser->profile_id][] = $sessionUser;
            if($sessionUser->user != null) {
                $user = $sessionUser->user->current();
                $sessionUsersCache[$sessionUser->session_user_id] = $user->getName();
                $usersCache[$sessionUser->user_id] = $user->getName();
            }
        }
        
        $criteria = $this->getService('AtCriterion')->fetchAllDependence('CriterionIndicator', null, array('cluster_id', 'name'));
        $criteriaCache = $criteria->getList('criterion_id', 'name');        
        
        if (count($profileIds)) {
            $profiles = $this->getService('AtProfile')->fetchAllDependence(array('Evaluation'), $this->getService('AtProfile')->quoteInto('profile_id in (?)', new Zend_Db_Expr(implode(',', $profileIds))));
            $this->view->competenceProfiles = $profiles;
        }
        
        $departmentsCache = array();
        foreach ($profiles as $profile) {
            unset($criterionTitles); //#17350
            $methods[$profile->profile_id] = array();
            if (!count($profile->evaluations)) continue;
            foreach ($profile->evaluations as $evaluation) {
                // т.к. этот отчёт используется тольк ов регулярной оценки
                if ($evaluation->programm_type == HM_Programm_ProgrammModel::TYPE_ASSESSMENT) {
                    $methods[$profile->profile_id][$evaluation->method] = true;
                }
            }
            if (in_array(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, array_keys($methods[$profile->profile_id]))) {

                $tableId = 'competence_profile_' . $profile->profile_id;
                $this->view->tables[$tableId]['head'] = array('title' => _('ФИО'), 'total' => _('Средний балл'));

                $avgResults = $criterionValues = array();
                foreach ($profileSesionUsers[$profile->profile_id] as $sessionUser) {
                    
                    $criterionValues = array();
                    if($sessionUser->user != null) {
                        $user = $sessionUser->user->current();
                        $sessionUsersCache[$sessionUser->session_user_id] = $user->getName();
                        if (count($sessionUser->criterionValues)) {
                            $criterionValues = $sessionUser->criterionValues->getList('criterion_id', 'value');
                            foreach($criterionValues as $criterion_id => &$v) { // приходится вычищать вручную, т.к. в запрос (для получения $sessionUsers) для fetchAllDependence условие не поставить, а для  fetchAllDependenceInnerJoin можно прицепить только 1 таблицу
                                foreach($sessionUser->criterionValues as $cv) {
                                    if($cv->criterion_id==$criterion_id && $cv->criterion_type!=HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
                                        unset($criterionValues[$criterion_id]);
                                        break;
                                    }
                                }
                            }
                            if (!isset($criterionTitles)) {
                                $criterionTitles = array();
                                foreach (array_keys($criterionValues) as $criterionId) {
                                    $criterionTitles[] = $criteriaCache[$criterionId];
                                }                   
                            }
                        }
                        $this->view->tables[$tableId][$sessionUser->session_user_id] = array('title' => $user->getName());
    
                        $sum = $count = 0;
                        foreach ($criteriaCache as $criterionId => $criterionName) {
    
                            if (!isset($criterionValues[$criterionId])) continue;
    
                            if ($criterionValues[$criterionId] != HM_Scale_Value_ValueModel::VALUE_NA) {
                                $this->view->tables[$tableId][$sessionUser->session_user_id][$criterionId] = $criterionValues[$criterionId];
                                $sum += $criterionValues[$criterionId];
                                $count++;
                            } else {
                                $this->view->tables[$tableId][$sessionUser->session_user_id][$criterionId] = '-';
                            }
                        }
                        
                        $this->view->tables[$tableId][$sessionUser->session_user_id]['total'] = $avgResults[$sessionUser->session_user_id] = $count ? round($sum/$count, 2) : '';
                    }
                }
//#18417
                $this->view->tables[$tableId]['head'] = (is_array($criterionTitles) && isset($this->view->tables[$tableId]['head']['title'])&& isset($this->view->tables[$tableId]['head']['total'])) ? array('title'=>$this->view->tables[$tableId]['head']['title']) + $criterionTitles + array('total'=>$this->view->tables[$tableId]['head']['total']): array();
//                $this->view->tables[$tableId]['head'] = is_array($criterionTitles) ? $criterionTitles + $this->view->tables[$tableId]['head'] : array();

                $top = $bottom = array();
                foreach ($avgResults as $sessionUserId => $result) {
                    if (strlen($result) && ($result >= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_TOP_COMPETENCES)) {
                        $top[$sessionUsersCache[$sessionUserId]] = $result;
                    }
                    if (strlen($result) && ($result <= HM_At_Evaluation_Method_CompetenceModel::THRESHOLD_BOTTOM_COMPETENCES)) {
                        $bottom[$sessionUsersCache[$sessionUserId]] = $result;
                    }
                }

                if (!count($top)) $top[_('нет')] = '';
                if (!count($bottom)) $bottom[_('нет')] = '';

                $this->view->lists['competence_top'] = $top;
                $this->view->lists['competence_bottom'] = $bottom;
            }

            // парные сравнения
            $departmentsCache[$profile->profile_id] = array();
            if (in_array(HM_At_Evaluation_EvaluationModel::TYPE_RATING, array_keys($methods[$profile->profile_id]))) {

                $ratings = $this->getService('AtSessionPairRating')->fetchAllDependence('User', array('session_id = ?' => $this->_session->session_id), 'criterion_id');
                $departmentIds = $ratings->getList('parent_soid');
                
                if (count($departmentIds)) {
                    $departmentsCache[$profile->profile_id] = $this->getService('Orgstructure')->fetchAll(array('soid IN (?)' => $departmentIds))->getList('soid', 'name');                    
                }

                foreach ($departmentIds as $departmentId) {
                    
                    $tableId = 'rating_profile_' . $profile->profile_id . '_' . $departmentId;
                    $this->view->tables[$tableId]['head'] = array(
                            'rating' => _('Рейтинг'),
                            'name' => _('ФИО'),
                            'ratio' => _('Результат, %'),
                    );
                    
                    $ratingsByCriteria = $ratingsTotal = array();
                     
                    foreach ($ratings as $rating) {
                        
                        if ($rating->parent_soid != $departmentId) continue;
                        
                        if ($rating->criterion_id && !isset($this->view->tables[$tableId]['head']["criterion_{$rating->criterion_id}"])) {
                            $this->view->tables[$tableId]['head']["criterion_{$rating->criterion_id}"] = $criteriaCache[$rating->criterion_id];
                        }
                        
                        if ($rating->criterion_id == HM_At_Session_Pair_Rating_RatingModel::TOTAL) {
                            $ratingsTotal[$rating->user_id] = $rating;
                        } else {
                            if (!isset($ratingsByCriteria[$rating->user_id])) $ratingsByCriteria[$rating->user_id] = array();
                            $ratingsByCriteria[$rating->user_id][$rating->criterion_id] = $rating;
                        }
                    }
                    
                    uasort($ratingsTotal, array('Session_ReportController', '_sortByRating'));
                    
                    foreach ($ratingsTotal as $userId => $ratingTotal) {
                        $this->view->tables[$tableId][$userId] = array(
                               'rating' => $ratingTotal->rating,
                               'name' => $usersCache[$ratingTotal->user_id],
                               'ratio' => array(
                                   'class' => self::_getPairCompareClass($ratingTotal->ratio),
                                   'value' => $ratingTotal->ratio,
                               )
                        );
                        foreach ($ratingsByCriteria[$userId] as $ratingByCriteria) {
                            $this->view->tables[$tableId][$userId]["criterion_{$ratingByCriteria->criterion_id}"] = array(
                                   'class' => self::_getPairCompareClass($ratingByCriteria->ratio),
                                   'value' => $ratingByCriteria->ratio,
                               );                        
                        }
                    }
                }
            }
        }
        
        $this->view->status = $this->_session->state;
        $this->view->methods = $methods;
        $this->view->departments = $departmentsCache;
    }

    public function getStaticDiagrammsAction()
    {
        $sessionUserId = $this->_getParam('session_user_id', 0);
        $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        if (!count($collection)) die();

        $this->_sessionUser = $collection->current();
        if (empty($this->_session) && count($this->_sessionUser->session)) {
            $this->_session = $this->_sessionUser->session->current();
        }

        $relationTypes = array(
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
        );

        $options = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, $this->_session->getOptionsModifier());        
        $results = $this->getService('AtEvaluation')->profileResultsByRelationType($this->_sessionUser, $options);

        $criteria = $this->getService('AtCriterion')->fetchAll(null, array('cluster_id', 'name'));
        $criteriaCache = $criteria->getList('criterion_id', 'name');
        $criteriaCluster = $criteria->getList('criterion_id', 'cluster_id');

        $results = $results['results'];
        $data = $graphs = array();
        $colors = HM_At_Evaluation_Method_CompetenceModel::getRelationTypeColors();
        $titles = HM_At_Evaluation_Method_CompetenceModel::getRelationTypesShort();
        foreach ($relationTypes as $relationType) {
            $graphs[$relationType] = array(
                'legend' => ucfirst($titles[$relationType]),
                'color' => $colors[$relationType],
            );
        }

        if (count($results)) {
            foreach ($results as $criterionId => $result) {
                $criterionData = array(
                    'title' => str_replace(",", ",\n", $criteriaCache[$criterionId]), //replace - не все лезло в диаграмму, мб надо это как-то по-умнее сделать
                );
                foreach ($relationTypes as $relationType) {
                    $criterionData[$relationType] = $result['criterion'][$relationType];
                }
                $data[$criteriaCluster[$criterionId]][] = $criterionData;
            }
        }

        foreach($data as $cluster=>$diagrammData) {
            $this->view->charts['competences'][$cluster] = array(
                'graphs' => $graphs,
                'data' => $diagrammData,
            );
        }

        $scaleId = $this->getService('Option')->getOption('competenceScaleId');
        $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue($scaleId);

        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('report-methods/diagramms.tpl');
    }


    public function myAction()
    {
        $collection = $this->getService('AtSessionUser')->fetchAllDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), array(
            'session_id = ?' => $this->_session->session_id,
            'user_id = ?' =>  $this->getService('User')->getCurrentUserId(),
        ));
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В данной оценочной сессии Вас никто не оценивает')));
            $this->_redirector->gotoSimple('my', 'list', 'session');
        } else {
            $this->_sessionUser = $collection->current();
        }
                        
//        $this->_userReportWord();
        $this->_userReport();
        
        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('report/user.tpl');
        
    }
    
    public function userWordAction()
    {
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) {
                $this->_redirect(Zend_Registry::get('baseUrl'));
        } 

        $sessionUserId = $this->_getParam('session_user_id', 0);
        $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник оценочной сессии не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'session');
            }
        }
        $this->_sessionUser = $collection->current();
        if (empty($this->_session) && count($this->_sessionUser->session)) {
            $this->_session = $this->_sessionUser->session->current();
        }
        
        $this->_userReportWord();
    }

    public function userAction()
    {
        /** @var HM_Acl $acl */
        $acl = $this->getService('Acl');

        /** @var HM_At_Session_User_UserService $sessionUserService */
        $sessionUserService = $this->getService('AtSessionUser');

        $atManager = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER);
        $supervisor = $acl->checkRoles(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR);

        // headless chrome обращается сюда с параметром /print/1/pdf/1/
        $isPrint = $this->getParam('print', 1);
        $isPdf = $this->getParam('pdf', 0);

        if (!($atManager || $supervisor) && !($isPrint && $isPdf)) {
            $this->_redirect(Zend_Registry::get('baseUrl'));
        }

        $sessionUserId = $this->_getParam('session_user_id', 0);

        if($this->getRequest()->isPost()) {
            $comment = $this->getParam('comment', '');
            if($comment) {
                $sessionUserService->addComment($sessionUserId, $comment);
            }
        }

        $collection = $sessionUserService->findDependence(['User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'], $sessionUserId);
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(['type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник оценочной сессии не найден')]);

            if ($acl->checkRoles([HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL])) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'session');
            }
        }

        $this->_sessionUser = $collection->current();

        if (empty($this->_session) && count($this->_sessionUser->session)) {
            $this->_session = $this->_sessionUser->session->current();
        }

        $this->_userReport();
    }


    public function myAnalyticsAction()
    {
        $collection = $this->getService('AtSessionUser')->fetchAllDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators'), array(
            'session_id = ?' => $this->_session->session_id,
            'user_id = ?' =>  $this->getService('User')->getCurrentUserId(),
        ));
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('В данной оценочной сессии Вас никто не оценивает')));
            $this->_redirector->gotoSimple('my', 'list', 'session');
        } else {
            $this->_sessionUser = $collection->current();
        }

        $this->_userAnalyticsReport();
        
        $this->_helper->viewRenderer->setNoRender();
        echo $this->view->render('report/user-analytics.tpl');        
    }
    
    public function userAnalyticsAction()
    {
        $submit = false;
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($params = $request->getParams()) {
                $submit = true;
            }
        }

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))) {
                $this->_redirect(Zend_Registry::get('baseUrl'));
        }

        $sessionUserId = $this->_getParam('session_user_id', 0);
        $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators'), $sessionUserId);
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник оценочной сессии не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'session');
            }
        }
        $this->_sessionUser = $collection->current();
        return $this->_userAnalyticsReport($submit);
    }

    public function assignSubjectsAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($params = $request->getParams()) {
                $subjectsIds = $params['subjects'];
                $message = _('Не выбраны курсы для назначения.');
                if (count($subjectsIds)) {
                    foreach ($subjectsIds as $subjectId) {
                        $sessionUser = $this->getService('AtSessionUser')->find($params['session_user_id']);
                        if (count($sessionUser)) {
                            $sessionUser = $sessionUser->current();
                            $this->getService('Subject')->assignUser($subjectId, $sessionUser->user_id);
                        }
                    }
                    $message = _('Пользователь назначен на выбранные курсы.');
                }

                $this->_flashMessenger->addMessage($message);
                $this->_redirector->gotoSimple('user-development', 'report', 'session', array('session_id' => $params['session_id'], 'session_user_id' => $params['session_user_id']));
            }
        }
    }

    public function userDevelopmentAction()
    {
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $this->_redirect(Zend_Registry::get('baseUrl'));
        }

        $sessionUserId = $this->_getParam('session_user_id', 0);
        $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators'), $sessionUserId);
        if (!count($collection)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник оценочной сессии не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ATMANAGER)) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'session');
            }
        }
        $this->_sessionUser = $collection->current();
        return $this->_userDevelopment();
    }
    
    public function matrixProgressAction()
    {
        $this->view->setHeader(_('Матрица успешности'));

        // На данный момент доступ к матрице только у супервайзера
        if (! $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR])) {
            $this->_flashMessenger->addMessage([
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Недостаточно прав для просмотра страницы')
            ]);
            $this->_redirector->gotoUrl('/', ['prependBase' => false]);
        }

        // У супервайзера должна быть задана область ответственности
        $orgstructureService = $this->getService('Orgstructure');
        try {
            $department = $orgstructureService->getDefaultParent();
        } catch (HM_Responsibility_ResponsibilityException $e) {
            $this->_flashMessenger->addMessage([
                'message' => $e->getMessage(),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR
            ]);
            $this->_redirector->gotoUrl('/', ['prependBase' => false]);
        }

        $sessionUsers = $employees = null;

        // Если есть подразделение и сотрудники в нём
        if ($department) {
            if (count($collection = $orgstructureService->fetchAll([
                'lft > ?' => $department->lft,
                'rgt < ?' => $department->rgt,
                'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
            ]))) {
                // Сотрудники, в т.ч. вложенных подразделений
                $employees = $collection->getList('mid', 'last_at_session_id');

                // Исходим из того, что ключ (session_id + user_id) вполне заменяет Pk(session_user_id) ad hoc
                $sessionUsers = $this->getService('AtSessionUser')->fetchAllHybrid(['Session', 'SessionEvents', 'Position', 'User'], 'UserKpi', 'User', [
                    'session_id IN (?)' => array_unique($employees),
                    'user_id IN (?)' => array_keys($employees),
                    'status = ?' => HM_At_Session_User_UserModel::STATUS_COMPLETED,
                ]);

            }
        }
        $this->view->sessionUsers = $sessionUsers;
        $this->view->employees = $employees;
    }

    public static function matrixProgressPlainify($data, $view = null)
    {
        $departments = $usersByDepartments = [];

        if (!empty($data['sessionUsers']) && !empty($data['employees'])) {

            $sessionUsers = $data['sessionUsers'];
            $employees = $data['employees'];
            $serviceContainer = Zend_Registry::get('serviceContainer');

            foreach ($sessionUsers as $sessionUser) {
                if (count($sessionUser->user)) {
                    $currentUser = $sessionUser->user->current();

                    // Если это не последняя сессия юзера, пропускаем
                    if ($employees[$currentUser->MID] != $sessionUser->session_id) continue;

                    $beginDate = (new HM_Date($sessionUser->session->current()->begin_date))->toString('dd.MM.yyyy');
                    $endDate = (new HM_Date($sessionUser->session->current()->end_date))->toString('dd.MM.yyyy');
                    $matrixBlock = $serviceContainer->getService('AtSessionUser')->getMatrixBlock($sessionUser->total_kpi, $sessionUser->total_competence);
                    $userDevelopmentLink = Zend_Registry::get('view')->url([
                        'baseUrl' => 'at',
                        'module' => 'session',
                        'controller' => 'report',
                        'action' => 'user-development',
                        'session_id' => $sessionUser->session_id,
                        'session_user_id' => $sessionUser->session_user_id
                    ]);
                    $userReportLink = Zend_Registry::get('view')->url([
                        'baseUrl' => 'at',
                        'module' => 'session',
                        'controller' => 'report',
                        'action' => 'user',
                        'session_user_id' => $sessionUser->session_user_id
                    ]);

                    $userData = [
                        'fio' => $currentUser->getName(),
                        'photo' => $currentUser->getPhoto()  ?: $currentUser->getDefaultPhoto(),
                        'matrixBlock' => $matrixBlock,
                        'beginDate' => $beginDate,
                        'endDate' => $endDate,
                        'userDevelopmentLink' => $userDevelopmentLink,
                        'userReportLink' => $userReportLink
                    ];

                    if (!empty($sessionUser->position)) { // temp
                        $usersByDepartments[$sessionUser->position->current()->owner_soid][] = $userData;
                    }
                }
            }
            if (count($usersByDepartments)) {
                $departments = $serviceContainer->getService('Orgstructure')->fetchAll(['soid IN (?)' => array_keys($usersByDepartments)])->getList('soid', 'name');
            }
        }

        return [
            'departments' => $departments,
            'usersByDepartments' => $usersByDepartments
        ];
    }

    public function eventAction()
    {
        if (!$sessionEventId = $this->_getParam('session_event_id', 0)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Анкета не найдена')));
            $this->_redirector->gotoSimple('index', 'list', 'session');
        }
        
        $event = $this->getService('AtSessionEvent')->findDependence(array(
            'Session',
            'SessionEventUser',
            'SessionUser',
            'SessionEventRespondent',
            'SessionRespondent',
            'EvaluationResult',
            'EvaluationIndicator',
            'EvaluationMemoResult'
        ), $sessionEventId)->current();

        $respondents = clone $event->sessionRespondent; //иначе ломается коллекция
        $respondents = $respondents->getList('user_id');

        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER))
            && !isset($respondents[$this->getService('User')->getCurrentUserId()])) {
                $this->_redirect(Zend_Registry::get('baseUrl'));
        } 

        $this->view->setHeader(_('Отчет о проведенном мероприятии'));
        $this->_helper->viewRenderer->setNoRender();
        switch ($event->method) {
            case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                $this->_eventCompetence($event);
                echo $this->render('event-competence');
            break;
            case HM_At_Evaluation_EvaluationModel::TYPE_TEST:
            case HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO:
            case HM_At_Evaluation_EvaluationModel::TYPE_FORM:
            case HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE:
                $this->_eventQuest($event);
            break;
        }
    }

    public function psychoAction()
    {
        if (!$sessionEventId = $this->_getParam('session_event_id', 0)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Анкета не найдена')));
            $this->_redirector->gotoSimple('index', 'list', 'session');
        }
        
        $event = $this->getService('AtSessionEvent')->findDependence(array(
            'Session',
            'SessionEventUser',
        ), $sessionEventId)->current();

        $sessionUserId = $event->session_user_id;
        $collection = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        $sessionUser = $collection->current();

        $this->_position = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->findDependence(array('Parent'), $sessionUser->position_id));
        $this->_profile = $this->getService('AtProfile')->getOne($this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_position->profile_id));
        if ($this->_position && count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $this->view->lists['general'] = array(
            _('ФИО') => $sessionUser->user->current()->getName(),
            _('Подразделение') => $positionName,
            _('Должность') => $this->_position ? $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : '') : $this->view->reportNoValue(),
            _('Профиль должности') => $this->_profile ? $this->_profile->name : $this->view->reportNoValue(),
        );
        $cycle = $this->_session->cycle ? $this->_session->cycle->current()->name : '';
        $sessionBeginDate = new HM_Date($this->_session->begin_date);
        $sessionEndDate = new HM_Date($this->_session->end_date);
        $this->view->lists['session'] = array(
            _('Оценочная сессия') => $this->_session->name,
            _('Оценочный период') => $cycle,
            _('Даты проведения оценки') => sprintf(_('c %s по %s'), $sessionBeginDate->toString('dd.MM.yyyy'), $sessionEndDate->toString('dd.MM.yyyy')),
            _('Дата подготовки отчета') => date('d.m.Y'),
        );

        $questAttempts = $this->getService('QuestAttempt')->fetchAll(array(
            'context_event_id = ?' => $event->session_event_id,        
            'context_type = ?' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ASSESSMENT,        
            'is_resultative = ?' => 1,        
        ));
        if (!count($questAttempts)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Попытка не найдена')));
            $this->_redirector->gotoSimple('index', 'list', 'session');
        }

        $questAttempt = $questAttempts->current();

        $results = $this->getService('QuestTypePsycho')->calcData(
            $questAttempt->quest_id,
            $questAttempt->attempt_id,
            $this->_getUserInfo($event->user->current())
        );

        $this->view->title = HM_Quest_Type_PsychoModel::getTypes()[$questAttempt->quest_id];
        $this->view->graph = $results['graph'];    
        $this->view->data = $results['data'];    
        $this->view->table = $results['table'];    

        $this->view->setHeader(_('Отчет о проведенном мероприятии'));
        $this->view->setBackUrl($this->view->url([
            'module' => 'session',
            'controller' => 'event',
            'action' => 'list',
            'session_id' => $this->_getParam('session_id'),
            'session_event_id' => null
        ]));
//        $this->_helper->viewRenderer->setNoRender();
    }

    function _getUserInfo($user) {
        $birthDate = $user->BirthDate;
        $gender = $user->Gender;
        $gender = $gender==HM_User_Metadata_MetadataModel::GENDER_MALE ? 'M' : ($gender==HM_User_Metadata_MetadataModel::GENDER_FEMALE ? 'F' : false); 
        $age = intval((time()-strtotime($birthDate))/(356*24*3600));

        return array('age'=>$age, 'gender'=>$gender);
    }    

    function _sortByRating($rating1, $rating2) {
        return ($rating1->rating < $rating2->rating) ? -1 : 1;
    }    
}