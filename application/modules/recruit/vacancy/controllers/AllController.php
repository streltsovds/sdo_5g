<?php

class Vacancy_AllController extends HM_Controller_Action_Vacancy
{
    protected $_candidateId = 0;
    protected $_candidate   = null;

    protected $_vacancyId   = 0;
    protected $_vacancy     = null;

    protected $superjob;
    protected $hh;

    public function init()
    {
        return parent::init();
    }

    protected function initHH()
    {
        if ($this->hh) {
            return;
        }

        $factory = $this->getService('RecruitServiceFactory');
        $config = Zend_Registry::get('config')->vacancy;
        $this->hh = $factory->getRecruitingService($config->externalSource, $config->api);
        $this->view->setHeader('Публикация на HeadHunter');
    }


    protected function initSuperjob()
    {
        if ($this->superjob) {
            return;
        }

        $factory = $this->getService('RecruitServiceFactory');
        $this->superjob = $factory->getRecruitingService('Superjob', HM_Recruit_RecruitingServices_PlacementBehavior::API_REST);

        if (!$this->superjob->getAuthToken()) {
            $this->superjob->getAuth();
        }

        $this->view->setHeader('Публикация на SuperJob');
    }



    public function responsesAction()
    {
        
        $sourceFilter = $this->_getParam('source_filter', '0');
        
        $candidateService = $this->getService('RecruitCandidate');
        
        $select = $candidateService->getSelect();
        
        $select->from(array('rc' => 'recruit_candidates'), array(
            'rc.resume_external_id',
        ));
        $select->joinInner(array('rvc' => 'recruit_vacancy_candidates'), 'rc.candidate_id = rvc.candidate_id', array());
        $select->where('rc.resume_external_id is not null');
        $select->where('rvc.vacancy_id = ?', $this->_vacancy->vacancy_id);
        
        $loadedResponses = $select->query()->fetchAll();
        
        foreach($loadedResponses as &$resp) {
            $resp = $resp['resume_external_id'];
        }
        
        $itemsSJ = array();
        if($sourceFilter == HM_Recruit_Provider_ProviderModel::ID_SUPERJOB || $sourceFilter == '0'){
            if ($this->_vacancy->superjob_vacancy_id) {
                $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('superjob');

                $responsesSJ = $huntingService->getVacancyResponse($this->_vacancy);
                foreach ($responsesSJ->objects as $key => &$resp) {
                    if(!in_array($resp->resume->id, $loadedResponses)){
                        $resp->source = HM_Recruit_Provider_ProviderModel::ID_SUPERJOB;
                        $itemsSJ[] = $resp;
                    }
                }
            }
        }

        $itemsHH = array();
        if($sourceFilter == HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER || $sourceFilter == '0'){
            if ($this->_vacancy->hh_vacancy_id) {
                $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');

                $responsesHH = $huntingService->getVacancyResponse($this->_vacancy);

                if (count($responsesHH->errors)) {
                    $error = array_shift($responsesHH->errors);
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _(sprintf('Ошибка загрузки откликов: %s', $error->value))
                    ));

                    $this->_redirector->gotoSimple('card', 'report', 'vacancy', array('vacancy_id' => $this->_vacancy->vacancy_id));

                }

                foreach ($responsesHH->items as $key => &$resp) {
                     if(!in_array($resp->resume->id, $loadedResponses)){ 
                        $resp->source = HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER;
                        $itemsHH[] = $resp;
                     }
                }
            }
        }

        $assignHhUrl = $this->view->url(array(
            'module'     => 'candidate',
            'controller' => 'list',
            'action'     => 'assign-all',
            'switcher'   => null,
            'status'     => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE,
        ));
        
        $assignToOtherVacancyUrl = $this->view->url(array(
            'module'       => 'candidate',
            'controller'   => 'list',
            'action'       => 'assign-all',
            'switcher'     => null,
            'status'       => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE,
            'otherVacancy' => 1,
        ));
        
        $discardResponseUrl = $this->view->url(array(
                'module'     => 'vacancy',
                'controller' => 'all',
                'action'     => 'discard-response',
        ));


        $actions = array(
            $assignHhUrl => array(
                'label' => _('Включить в сессию подбора'),
                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в данную сессию подбора?'),
            ),
            $assignToOtherVacancyUrl => array(
                'label' => _('Включить в другую сессию подбора'),
                'confirm' => _('Вы уверены, что хотите включить выбранных кандидатов в выбранную сессию подбора?'),
            ),
            $discardResponseUrl => array(
                'label' => _('Отклонить отклик'),
                'confirm' => _('Вы уверены, что хотите отклонить отклик? Это действие отклоняет отклик кандидата на внешнем ресурсе, отменить его можно только через интерфейс ресурса.'),
            )
        );


        $hhIds = array();
        $sjIds = array();

        if(count($itemsHH)){
            foreach ($itemsHH as $itemHH) {
                $hhIds[] = $itemHH->resume->id;
            }
        } else {
            $hhIds[] = 0; 
        }
        
        if(count($itemsSJ)){
            foreach ($itemsSJ as $itemSJ) {
                $sjIds[] = $itemSJ->resume->id;
            }
        } else {
            $sjIds[] = 0; 
        }

//        $candidateService = $this->getService('RecruitCandidate');
//        $ignore = $candidateService->fetchAll(
//            $candidateService->quoteInto(
//                array(
//                    ' (source = ? ', ' AND resume_external_id IN (?)) ',
//                    ' OR (source = ? ', ' AND resume_external_id IN (?))'
//                ),
//                array(
//                    HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER, $hhIds,
//                    HM_Recruit_Provider_ProviderModel::ID_SUPERJOB, $sjIds,
//                )
//            )
//        )->getList('resume_external_id', 'source');

        $allResponses = array_merge($itemsHH, $itemsSJ);

//        foreach($ignore as $ignoreId => $source) {
//            foreach($allResponses as $key => $response) {
//                if($source == $response->source && $response->resume->id == $ignoreId) {
//                    unset($allResponses[$key]);
//                    continue;
//                }
//            }
//        }

        $sourceFilter = new Zend_Form_Element_Select('source_filter', 
            array(
                'Label' => _('Показывать отклики:'),
                'multiOptions' => array(
                    '0'                                              => _('Все'),
                    HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER => 'HeadHunter',
                    HM_Recruit_Provider_ProviderModel::ID_SUPERJOB   => 'SuperJob',
                ),
                'Filters' => array('StripTags'),
                'value'   => $sourceFilter
            )
        );
        
        $subAction = new Zend_Form_Element_Select('vacancy_id', 
            array(
                'multiOptions' => $vacancies = $this->getService('Recruiter')->getVacanciesForDropdownSelect(),
                'Filters' => array('StripTags'),
                'hidden' => true,
                'disabled' => true
            )
        );
        
        foreach($subAction->getDecorators() as $name => $decorator){
            if($name != 'Zend_Form_Decorator_ViewHelper'){
                $subAction->removeDecorator($name);
            }
        }
        
        $customFormElements = array(
            $subAction->render()
        );
        
            
        $submit = new Zend_Form_Element_Submit('submit', array('Label' => _('Применить')));         

        $this->view->responses = $allResponses;
        $this->view->actions   = $actions;
        
        $this->view->sourceFilter            = $sourceFilter->render();
        $this->view->submit                  = $submit->render();
        $this->view->customFormElements      = $customFormElements;
        $this->view->assignToOtherVacancyUrl = $assignToOtherVacancyUrl;
    }
    
    public function loadedResponsesAction()
    {
        $vacancyId = $this->_getParam('vacancy_id');
        
        $candidateService = $this->getService('RecruitCandidate');
        $select = $candidateService->getSelect();
            
        $select->from(array('rc' => 'recruit_candidates'), array(
            'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'rc.*'
        ));
        
        $select->joinInner(array('rvc' => 'recruit_vacancy_candidates'), 'rc.candidate_id = rvc.candidate_id', array());
        $select->joinInner(array('p' => 'People'), 'rc.user_id = p.MID', array());
        
        $select->where('rvc.vacancy_id = ?', $vacancyId);
        $select->where('rvc.status = ?', HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON);
        $select->where('rvc.result >= 0 OR rvc.result IS NULL');
        
        $responses = $select->query()->fetchAll();
        
        $discardResponseUrl = $this->view->url(array(
                'module'     => 'vacancy',
                'controller' => 'all',
                'action'     => 'discard-loaded-response',
        ));


        $actions = array(
            $discardResponseUrl => array(
                'label' => _('Отклонить отклик'),
                'confirm' => _('Вы уверены, что хотите отклонить отклик? Это действие отклоняет отклик кандидата на внешнем ресурсе, отменить его можно только через интерфейс ресурса.'),
            )
        );
        
        $this->view->actions   = $actions;
        $this->view->responses = $responses;
    }
    
    
    public function discardResponseAction()
    {
        $vacancyId = $this->_getParam('vacancy_id');
        
        $resumeIds = explode(',', $this->_getParam('postMassIds_grid', ''));
        
        foreach($resumeIds as $resumeId){
            $resumeIdParts = explode(':',$resumeId);

            switch ($resumeIdParts[0]) {
                case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                    $this->_discardResponseHh($resumeIdParts[1]);
                    break;
                case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                    $this->_discardResponseSj($resumeIdParts[1]);
                    break;
            }
        }
        
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Отклики успешно отклонены!')
        ));

        $this->_redirector->gotoSimple('responses', 'all', 'vacancy', array('vacancy_id' => $vacancyId));
        
    }
    
    public function discardLoadedResponseAction() {
        $vacancyId = $this->_getParam('vacancy_id');
        
        $vacancyAssignService = $this->getService('RecruitVacancyAssign');
        $candidateService     = $this->getService('RecruitCandidate');
        
        $resumeIds = explode(',', $this->_getParam('postMassIds_grid', ''));
        
        foreach($resumeIds as $resumeId){
            $resumeIdParts = explode(':',$resumeId);
            
            $candidate = $candidateService->fetchAll(array(
                'candidate_id = ?' => $resumeIdParts[1]
            ))->current();
            
            $vacancyAssignService->updateWhere(
                array(
                    'result' => HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT
                ),
                array(
                    'status'       => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON,
                    'candidate_id' => $resumeIdParts[1],
                    'vacancy_id'   => $vacancyId,
                    
                )
            );
            if($candidate->resume_external_id){
                switch ($resumeIdParts[0]) {
                    case HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER:
                        $this->_discardResponseHh($candidate->hh_negotiation_id);
                        break;
                    case HM_Recruit_Provider_ProviderModel::ID_SUPERJOB:
                        $this->_discardResponseSj($candidate->resume_external_id);
                        break;
                }
            }
        }
        
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Отклики успешно отклонены!')
        ));

        $this->_redirector->gotoSimple('responses', 'all', 'vacancy', array('vacancy_id' => $vacancyId));
        
    }

    protected function _discardResponseHh($negotiationId){
        if($negotiationId){
            $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');
        
            $negotiation = $huntingService->getNegotiation(array('negotiationId' => $negotiationId));
            
            if($negotiation->actions){
                foreach($negotiation->actions as $action){
                    if($action->id == 'discard_by_employer' && $action->enabled){
                        $result = $huntingService->discardVacancyResponse(array('negotiationId' => $negotiationId));
                        break;
                    }
                }
            }
        }
    }

    protected function _discardResponseSj($resumeId){
        if($resumeId){
            $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('superjob');
            $huntingService->discardVacancyResponse($resumeId);
        }
    }

}