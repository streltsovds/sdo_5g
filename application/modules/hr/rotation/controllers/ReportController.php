<?php
class Rotation_ReportController extends HM_Controller_Action_Crud
{
    protected $_rotation;

    protected $service     = 'HrRotation';
    protected $idParamName = 'rotation_id';
    protected $idFieldName = 'hrrotation';
    protected $id          = 0;

    public function init()
    {
        parent::init();
        
        $rotationId = $this->_getParam('rotation_id', 0);
        $this->view->print = $print = $this->_getParam('print', 0);
        if (count($collection = $this->getService('HrRotation')->findDependence(array(/*'Session',*/ 'User', ''), $rotationId))) {
            $this->_rotation = $collection->current();
            //$this->_session = $this->_rotation->session->current();

            if (!$this->isAjaxRequest() && !$print) {
                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->_rotation->rotation_id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $this->_rotation
                    )
                );
            }
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия ротации')));
            $this->_redirector->gotoSimple('index', 'list', 'rotation');
        }
    }

    public function indexAction()
    {
        $position = $this->getService('Orgstructure')->find($this->_rotation->position_id)->current();
        $rotationName = $position->name;
        $this->view->setHeader(_('Сессия ротации') . ' "' . $rotationName . '"');

        $this->view->rotation = $this->_rotation;
        if (count($this->_rotation->user)) {
            $user = $this->view->user = $this->_rotation->user->current();
            $this->view->setSubHeader($user->getName());
        }
        
        if (count($collection = $this->getService('ProcessStep')->fetchAll(array(
            'item_id = ?' => $this->_rotation->rotation_id,
            'process_type = ?' => HM_Process_ProcessModel::PROCESS_PROGRAMM_ROTATION,
            'step = ?' => 'HM_Hr_Rotation_State_Publish',
        )))) {
            $step = $collection->current();
            $assessmentDate = new HM_Date($step->date_begin);
            $assessmentDate = $assessmentDate->toString('dd.MM.Y');
        }

        if (count($collection = $this->getService('Orgstructure')->fetchAllHybrid(array('Parent'), 'Category', null, array('soid = ?' => $this->_rotation->position_id)))) {

            $this->_position = $collection->current(); // позиция в оргструктуре
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
            
            $this->_profile = $this->getService('Orgstructure')->findDependence(array('Evaluation', 'CriterionValue', 'Category', 'Quest'), $this->_rotation->profile_id);

            if (count($this->_position->category)) {
                $category = $this->_position->category->current(); // категория должности
            }
        }
        
        if ($this->_rotation->evaluation_user_id &&
            count($collection = $this->getService('Orgstructure')->fetchAllDependence(array('User'), array('mid = ?' => $this->_rotation->evaluation_user_id)))
        ) {

            $curator = $collection->current(); // позиция в оргструктуре (может быть несколько :/)
            if (count($curator->user)) {
                $curatorUser = $curator->user->current();
            }
        }

        $collection = $this->getService('HrRotation')->findManyToMany('Hr', 'RecruiterAssign', $this->_rotation->rotation_id);

        $recruiters = array();
        if (count($collection) && count($collection->current()->recruiters)) {
            $hrMids = $collection->current()->recruiters->getList('user_id');
            $collection = $collection = $this->getService('User')->fetchAll(array('MID IN (?)' => $hrMids));
            foreach ($collection as $user) {
                $recruiters[] = sprintf('%s %s',
                    $this->view->cardLink($this->view->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'gridmod' => null, 'user_id' => $hr->MID, 'baseUrl' => '', 'rotation_id' => null))),
                    $user->getName()
                );
            }
        }

        $openDate = new HM_Date($this->_rotation->created);
        $openDate = $openDate->toString('dd.MM.Y');              
//         $closeDate = new HM_Date($this->_rotation->close_date);
//         $closeDate = $closeDate->toString('dd.MM.Y');  

        
        /************************************/
        
        $lists = array(
            'general' => array(
                _('ФИО участника') => $user ? $user->getName() : '',
                _('Целевая должность') => $this->_position->name,
                _('Целевое подразделение') => $parent ? $parent->name : $this->view->reportNoValue(),
                _('Дата начала сессии ротации') => date('d.m.Y', strtotime($this->_rotation->begin_date)),
                _('Дата завершения сессии ротации') => date('d.m.Y', strtotime($this->_rotation->end_date)),
            )
        );

        $this->view->lists = $lists;
    }
    
    public function userAction()
    {
        $methods = array();
        $this->view->setHeader(_('Индивидуальный отчет'));

        $rotationId = $this->_getParam('rotation_id');
        $rotation = $this->getService('HrRotation')->findDependence(array('SessionUser', 'User'), $rotationId)->current();
        
        if (count($rotation->user) && count($rotation->sessionUser)) {
            $user = $rotation->user->current();
            $sessionUserId = $rotation->sessionUser->current()->session_user_id;
            $sessionUser = $this->getService('AtSessionUser')->findDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), $sessionUserId);
        }
        if (!count($sessionUser)) {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Участник сессии подбора не найден')));
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) {
                $this->_redirector->gotoSimple('my', 'list', 'session');
            } else {
                $this->_redirector->gotoSimple('index', 'list', 'rotation');
            }
        }
        
        $this->_sessionUser = $sessionUser->current();
        $this->_position = $this->getService('Orgstructure')->findDependence(array('Parent'), $this->_rotation->position_id)->current();
        $this->_profile = $this->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_rotation->profile_id)->current();

        if (count($this->_position->parent) && !is_null($this->_position->parent) ) {
            $positionName = $this->_position->parent->current()->name;
        }
        $this->view->lists['general'] = array(
            _('ФИО') => $user->getName(),
            _('Подразделение') => $positionName,
            _('Должность') => $this->_position->name . ($this->_position->is_manager ? ' (' . _('руководитель') . ')' : ''),
            _('Профиль должности') => $this->_profile->name,
        );

        $processStepService = $this->getService('ProcessStep');
        
        $processes = $processStepService->fetchAll($processStepService->quoteInto(
            array(
                'process_type = ?',
                ' AND item_id = ?',
            ),
            array(
                HM_Process_ProcessModel::PROCESS_PROGRAMM_ADAPTING,
                $rotationId,
            )
        ));
        
        $dateBeginMin = 0;
        $dateEndMax   = 0;
        foreach($processes as $process){
            $dateBegin = strtotime($process->date_begin);
            $dateEnd   = strtotime($process->date_end);
            
            if($dateEnd > $dateEndMax){
                $dateEndMax = $dateEnd;
            }
            
            if($dateBegin < $dateBeginMin || $dateBeginMin == 0){
                $dateBeginMin = $dateBegin;
            }
        }
        
        
        $sessionBeginDate = new HM_Date($dateBeginMin);
        $sessionEndDate = new HM_Date($dateEndMax);
        
        $this->view->lists['session'] = array(
            _('Даты проведения ротации') => sprintf(_('c %s по %s'), $sessionBeginDate->toString('dd.MM.yyyy'), $sessionEndDate->toString('dd.MM.yyyy')),
            _('Дата подготовки отчета') => date('d.m.Y'),
        );

        $this->view->scaleMaxValue = Zend_Registry::get('serviceContainer')->getService('Scale')->getMaxValue(
            Zend_Registry::get('serviceContainer')->getService('Option')->getOption('competenceScaleId', HM_Option_OptionModel::MODIFIER_RECRUIT)        
        );

        $methods = array();
        $params = array('sessionUser' => $this->_sessionUser, 'profile' => $this->_profile);
        if (1/*$programm = $this->getService('Programm')->getOne($this->getService('Programm')->fetchAllDependence('Event', array(
            'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ADAPTING,          
            'item_type = ?' => HM_Programm_ProgrammModel::ITEM_TYPE_NEWCOMER,          
            'item_id = ?' => $this->_rotation->rotation_id,          
        )))*/) {
            if (1/*count($programm->events)*/) {
//                $evaluationIds = $programm->events->getList('ordr', 'item_id');
//                ksort($evaluationIds);
//                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('evaluation_type_id IN (?)' => $evaluationIds))->asArrayOfObjects();
                $evaluations = $this->getService('AtEvaluation')->fetchAll(array('rotation_id  = ?' => $rotationId))->asArrayOfObjects();
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
