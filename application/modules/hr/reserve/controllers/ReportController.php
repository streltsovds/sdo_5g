<?php
class Reserve_ReportController extends HM_Controller_Action_Reserve
{
    use HM_Controller_Action_Trait_Report;

    public function init()
    {
        $this->initReport();
        parent::init();
    }

    public function indexAction()
    {
        $this->view->setHeader($this->_reserve->name);
        $this->view->reserve = $this->_reserve;

        if (count($this->_reserve->reservePosition)) {
            $reservePosition = $this->_reserve->reservePosition->current();
            $customRespondents = unserialize($reservePosition->custom_respondents);
        }

        if (count($this->_reserve->profile)) {
            $profile = $this->_reserve->profile->current();
        }

        if (count($this->_reserve->cycle)) {
            $cycle = $this->_reserve->cycle->current();
            $beginDate = new HM_Date($cycle->begin_date);
            $endDate = new HM_Date($cycle->end_date);
            $period = sprintf(_('%s (c %s по %s)'), $cycle->name, $beginDate->toString('dd.MM.Y'), $endDate->toString('dd.MM.Y'));
        }

        if (count($this->_reserve->user)) {
            $user = $this->view->user = $this->_reserve->user->current();
        }
        
        if ($customRespondents && count($customRespondents)) {

            $collection = $this->getService('Orgstructure')->fetchAllDependence(array('User'), array(
                'mid IN (?)' => $customRespondents,
            ));

            $managerUsers = array();
            if (count($collection)) {
                foreach ($collection as $manager) {
                    if (count($manager->user)) {
                        $managerUsers[] = sprintf('%s %s (%s)',
                            $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $manager->mid, 'baseUrl' => '', 'reserve_id' => null))),
                            $manager->user->current()->getName(),
                            $manager->name
                        );
                    }
                }
            }
        }

        $createDate = new HM_Date($this->_reserve->created); // (#28519) new HM_Date($cycle->begin_date);
        $createDate = $createDate->toString('dd.MM.Y');

        /************************************/
        
        $this->view->lists['general'] = array(
            _('ФИО участника') => $user ?
                sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $user->MID, 'baseUrl' => '', 'reserve_id' => null))),
                    $user->getName()
                ) : '',
            _('Дата включения пользователя в сессию КР') => $createDate,
            _('Профиль должности КР') => $profile ? $profile->name : $this->view->reportNoValue(),
            _('Период сессии КР') => $period ? : $this->view->reportNoValue(),
            _('Куратор(ы) сессии КР') => count($managerUsers) ? implode('<br><br>', $managerUsers): $this->view->reportNoValue(),
        );
    }
    
    public function userAction()
    {
        $methods = array();
        $this->view->setHeader(_('Отчет по итогам участия в сессии КР'));

        $reserveId = $this->_getParam('reserve_id');
        $reserve = $this->getService('HrReserve')->findDependence(array('SessionUser', 'User'), $reserveId)->current();
        
        if (count($reserve->user) && count($reserve->sessionUser)) {
            $user = $reserve->user->current();
            $sessionUserId = $reserve->sessionUser->current()->session_user_id;
            $sessionUser = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        }
        if (!count($sessionUser)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник сессии подбора не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'reserve');
            }
        }
        
        $this->_sessionUser = $sessionUser->current();
        $this->_position = $this->getService('Orgstructure')->findDependence(array('Parent'), $this->_reserve->position_id)->current();
        $this->_profile = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_reserve->profile_id)->current();

        if (count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $this->view->lists['general'] = array(
            _('ФИО') => $user->getName(),
            _('Подразделение') => $positionName,
            _('Должность') => $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : ''),
            _('Профиль должности') => $this->_profile->name,
        );

        $this->getService('Process')->initProcess($reserve);
        $process = $reserve->getProcess();

        $processStateData = array();
        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $reserve->reserve_id,
        )));

        if ($state && count($state->stateData)) {
            foreach ($state->stateData as $item) {
                $processStateData[$item->state] = $item;
            }
        }

        $dateBeginState   = $processStateData['HM_Hr_Reserve_State_Open'];
        $dateEndState     = $processStateData['HM_Hr_Reserve_State_Result'];
        $dateReportState  = $processStateData['HM_Hr_Reserve_State_Publish'];
        $sessionBeginDate = new HM_Date($dateBeginState->begin_date);
        $sessionEndDate   = new HM_Date($dateEndState->end_date);
        $reportDate = new HM_Date($dateReportState->end_date);

        if (count($this->_reserve->cycle)) {
            $cycle = $this->_reserve->cycle->current();
            $beginDate = new HM_Date($cycle->begin_date);
            $endDate = new HM_Date($cycle->end_date);
            $period = sprintf(_('%s (c %s по %s)'), $cycle->name, $beginDate->toString('dd.MM.Y'), $endDate->toString('dd.MM.Y'));
        }

        $this->view->lists['session'] = array(
            _('Период сессии КР') => $period ? : $this->view->reportNoValue(),
            _('Дата подготовки отчета') => $reportDate->toString('dd.MM.yyyy'),
        );

        $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue(
            Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId', HM_Option_OptionModel::MODIFIER_RECRUIT)        
        );

        $methods = array();
        $params = array('sessionUser' => $this->_sessionUser, 'profile' => $this->_profile);
        if ($programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', array(
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_RESERVE,
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE,
            'item_id = ?' => $this->_reserve->reserve_id,
        )))) {
            if (count($programm->events)) {
                $evaluationIds = $programm->events->getList('ordr', 'item_id');
                ksort($evaluationIds);
                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('evaluation_type_id IN (?)' => $evaluationIds))->asArrayOfObjects();
                foreach ($evaluationIds as $evaluationId) {
                    $evaluation = $evaluations[$evaluationId];
                    switch ($evaluation->method) {
                        case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                            // какие срезы выводить на круговых диаграммах
                            $params['relationTypes'] = array(
                                 HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF,
                                 HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_RESERVE,
                            );
                        break;
                        case HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE:
                            // пока истользуем отчёт от TYPE_FORM
                            $evaluation->method = HM_At_Evaluation_EvaluationModel::TYPE_FORM;                           
                        break;
                    }
                    $params['evaluation'] = $evaluation;
                    $methods[$evaluation->method] = $params; // это совершенно не годится для оценки 360 (когда много evaluation'ов на одну секцию в отчёте), если она здесь когда-нибудь появится
                }
            }
        }        

        $this->view->texts['general'] = $this->_session->report_comment;
        $this->view->texts['competence_general'] = $this->getService('Option')->getOption('competenceReportComment', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $this->view->sessionUser = $this->_sessionUser;
        $this->view->methods = $methods;
    }
    
}
