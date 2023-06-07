<?php
class Newcomer_ReportController extends HM_Controller_Action_Newcomer
{
    use HM_Controller_Action_Trait_Report;

    public function init()
    {
        $this->initReport();
        return parent::init();
    }

    public function indexAction()
    {
        $this->view->setHeader(_('Сессия адаптации'));

        $this->view->newcomer = $this->_newcomer;
        if (count($this->_newcomer->user)) {
            $user = $this->view->user = $this->_newcomer->user->current();
        }

        $this->getService('Process')->initProcess($this->_newcomer);
        $process = $this->_newcomer->getProcess();

        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $this->_newcomer->newcomer_id,
        )));
        $stateDates = array();
        if ($state && count($state->stateData)) {
            $stateDates = $state->stateData->getList('state', 'begin_date_planned');
        }


            if (count($collection = $this->getService('Orgstructure')->fetchAllDependence(array('Parent'), array('soid = ?' => $this->_newcomer->position_id)))) {

            $this->_position = $collection->current(); // позиция в оргструктуре
            $positionDate = new HM_Date($this->_position->position_date);
            $positionDate = $positionDate->toString('dd.MM.Y');
            if (count($this->_position->parent)) {
                $parent = $this->_position->parent->current(); // подразделение

                $collection = $this->getService('Orgstructure')->fetchAllDependence(array('User'), array(
                    'owner_soid = ?' => $parent->soid,
                    'is_manager = ?' => 1,
                ));

                if (count($collection)) {
                    $manager = $collection->current();
                    if (count($manager->user)) {
                        $managerUser = $manager->user->current();
                    }
                }
            }
            
            $collection = $this->getService('AtProfile')->findDependence(array('Category'), $this->_newcomer->profile_id);

            if (count($collection)) {
                $profile = $collection->current();
                if (count($profile->category)) {
                    $category = $profile->category->current();
                }
            }
        }
        
        if ($this->_newcomer->evaluation_user_id &&
            count($collection = $this->getService('Orgstructure')->fetchAllDependence(array('User'), array('mid = ?' => $this->_newcomer->evaluation_user_id)))
        ) {

            $curator = $collection->current(); // позиция в оргструктуре (может быть несколько :/)
            if (count($curator->user)) {
                $curatorUser = $curator->user->current();
            }
        }

        $collection = $this->getService('RecruitNewcomer')->findManyToMany('Recruiter', 'RecruiterAssign', $this->_newcomer->newcomer_id);

        $recruiters = array();
        if (count($collection) && count($collection->current()->recruiters)) {
            $recruiterMids = $collection->current()->recruiters->getList('user_id');
            $collection = $collection = $this->getService('User')->fetchAll(array('MID IN (?)' => $recruiterMids));
            foreach ($collection as $recruiter) {
                $recruiters[] = sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $recruiter->MID, 'baseUrl' => '', 'newcomer_id' => null))),
                    $recruiter->getName()
                );
            }
        }

        $openDate = new HM_Date($this->_newcomer->created);
        $openDate = $openDate->toString('dd.MM.Y');

        $assessmentDate = false;
        if (isset($stateDates['HM_Recruit_Newcomer_State_Publish'])) {
            $assessmentDate = new HM_Date($stateDates['HM_Recruit_Newcomer_State_Publish']);
            $assessmentDate = $assessmentDate->toString('dd.MM.Y');
        }

        /************************************/
        
        $this->view->lists['general'] = array(
            _('ФИО участника') => $user ?
                sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $user->MID, 'baseUrl' => '', 'newcomer_id' => null))),
                    $user->getName()
                ) : '',
            _('Название должности') => $this->_newcomer->name,
            _('Профиль должности') => $profile ? $profile->name : $this->view->reportNoValue(),
            _('Категория должности') => $category ? $category->name : $this->view->reportNoValue(),
            _('Подразделение') => $parent ? $parent->name : $this->view->reportNoValue(),
            _('Руководитель подразделения') => $managerUser ?
                sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $managerUser->MID, 'baseUrl' => '', 'newcomer_id' => null))),
                    $managerUser->getName()
            ) : '',
            _('Куратор адаптации') => ($curator && $curatorUser) ?
                sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $curatorUser->MID, 'baseUrl' => '', 'newcomer_id' => null))),
                    $curatorUser->getName()
            ) : '',
            _('Специалист по адаптации') => implode(', ', $recruiters),
            _('Дата начала сессии адаптации') => $openDate,
            _('Плановая дата проведения оценки') => $assessmentDate ? : '',
            _('Дата приёма на работу') => $positionDate,
        );
    }
    
    public function userAction()
    {
        $methods = array();
        $this->view->setHeader(_('Отчет о достижении целей'));

        $newcomerId = $this->_getParam('newcomer_id');
        $newcomer = $this->getService('RecruitNewcomer')->findDependence(array('SessionUser', 'User'), $newcomerId)->current();
        
        if (count($newcomer->user) && count($newcomer->sessionUser)) {
            $user = $newcomer->user->current();
            $sessionUserId = $newcomer->sessionUser->current()->session_user_id;
            $sessionUser = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        }
        if (!count($sessionUser)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник сессии подбора не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'newcomer');
            }
        }
        
        $this->_sessionUser = $sessionUser->current();
        $this->_position = $this->getService('Orgstructure')->findDependence(array('Parent'), $this->_newcomer->position_id)->current();
        $this->_profile = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_newcomer->profile_id)->current();

        if (count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $this->view->lists['general'] = array(
            _('ФИО') => $user->getName(),
            _('Подразделение') => $positionName,
            _('Должность') => $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : ''),
            _('Профиль должности') => $this->_profile->name,
        );

        $this->getService('Process')->initProcess($newcomer);
        $process = $newcomer->getProcess();

        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $newcomerId,
        )));
        $stateDates = array();
        if ($state && count($state->stateData)) {
            $stateDates = $state->stateData->getList('state', 'begin_date_planned');
        }

        if (count($collection = $this->getService('Orgstructure')->fetchAllDependence(array('Parent'), array('soid = ?' => $this->_newcomer->position_id)))) {
            $this->_position = $collection->current(); // позиция в оргструктуре
            $positionDate = new HM_Date($this->_position->position_date);
            $positionDate = $positionDate->toString('dd.MM.Y');
        }

        $openDate = new HM_Date($newcomer->created);
        $openDate = $openDate->toString('dd.MM.Y');

        $assessmentDate = false;
        if (isset($stateDates['HM_Recruit_Newcomer_State_Publish'])) {
            $assessmentDate = new HM_Date($stateDates['HM_Recruit_Newcomer_State_Publish']);
            $assessmentDate = $assessmentDate->toString('dd.MM.Y');
        }

        $this->view->lists['session'] = array(
            _('Дата начала сессии адаптации') => $openDate,
            _('Плановая дата проведения оценки') => $assessmentDate ? : '',
            _('Дата приёма на работу') => $positionDate,
        );

        $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue(
            Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId', HM_Option_OptionModel::MODIFIER_RECRUIT)        
        );

        $methods = array();
        $params = array('sessionUser' => $this->_sessionUser, 'profile' => $this->_profile);
        if (1/*$programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', array(
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ADAPTING,          
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,          
            'item_id = ?' => $this->_newcomer->newcomer_id,          
        )))*/) {
            if (1/*count($programm->events)*/) {
//                $evaluationIds = $programm->events->getList('ordr', 'item_id');
//                ksort($evaluationIds);
//                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('evaluation_type_id IN (?)' => $evaluationIds))->asArrayOfObjects();
                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('newcomer_id  = ?' => $newcomerId))->asArrayOfObjects();
//                foreach ($evaluationIds as $evaluationId) {
                foreach ($evaluations as $evaluationId=>$evaluation) {
//                    $evaluation = $evaluations[$evaluationId];
                    switch ($evaluation->method) {
                        case HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE:
                            // какие срезы выводить на круговых диаграммах
                            $params['relationTypes'] = array(
                                 HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_RECRUITER,
                            );                            
                        break;
                        case HM_At_Evaluation_EvaluationModel::TYPE_FINALIZE:
                            // пока истользуем отчёт от TYPE_FORM
                            $evaluation->method = HM_At_Evaluation_EvaluationModel::TYPE_FORM;                           
                        break;
                    }
                    $params['evaluation'] = $evaluation;
                    $methods[] = $params; // это совершенно не годится для оценки 360 (когда много evaluation'ов на одну секцию в отчёте), если она здесь когда-нибудь появится
                }
            }
        }        

        $this->view->texts['general'] = $this->_session->report_comment;
        $this->view->texts['competence_general'] = $this->getService('Option')->getOption('competenceReportComment', HM_Option_OptionModel::MODIFIER_RECRUIT);
        $this->view->sessionUser = $this->_sessionUser;
        $this->view->methods = $methods;
    }
    
}
