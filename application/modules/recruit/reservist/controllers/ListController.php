<?php

class Reservist_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_reservistId = 0;
    protected $_reservist = null;

    public function init() 
    {
        $form = new HM_Form_Reservist();
        $this->_setForm($form);

        $this->_reservistId = $this->_getParam('reservist_id', 0);
        parent::init();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index');
    }

    public function delete($id) 
    {
        $this->getService('RecruitReservist')->delete($id);
    }

    public function indexAction() 
    {
        
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'fio_ASC');
        }        
        
        $select = $this->getService('RecruitReservist')->getSelect();
        $from = array(
            'reservist_id' => 'rr.reservist_id',
            'fio' => 'rr.fio',
            'company' => 'rr.company',
            'department' => 'rr.department',
            'brigade' => 'rr.brigade',
            'position' => 'rr.position',
            'region' => 'rr.region'
        );

        $select->from(array('rr' => 'recruit_reservists'), $from)
            ->group(array(
                'rr.reservist_id',
                'rr.fio',
                'rr.company',
                'rr.department',
                'rr.brigade',
                'rr.position',
                'rr.region'
        ));

        $columnsOptions = array(
            'reservist_id' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО'),
                'callback' => array(
                    'function' => array($this, 'updateFio'),
                    'params' => array('{{reservist_id}}', '{{fio}}')
                )
            ),
            'company' => array(
                'title' => _('Наименование организации')
            ),
            'department' => array(
                'title' => _('Структурное подразделение')
            ),
            'brigade' => array(
                'title' => _('Бригада')
            ),
            'position' => array(
                'title' => _('Должность')
            ),
            'region' => array(
                'title' => _('Регион проживания')
            ),
        );

        $filters = array(
            'fio' => null,
            'company' => null,
            'department' => null,
            'brigade' => null,
            'position' => null,
            'region' => null,
        );

        $grid = $this->getGrid(
            $select, 
            $columnsOptions, 
            $filters
        );

        $vacancies = $this->getService('Recruiter')->getVacanciesForDropdownSelect();
        if (count($vacancies)) {

            $grid->addMassAction(array(
                'module' => 'reservist',
                'controller' => 'list',
                'action' => 'assign',
            ),
                _('Включить в сессию подбора'),
                _('Вы уверены, что хотите включить выбранных резервистов в данную сессию подбора?')
            );

            $grid->addSubMassActionSelect(array($this->view->url(array('action' => 'assign'))), 'assign_vacancy_id', $vacancies, false);

            $grid->addMassAction(
                array(
                    'module' => 'reservist',
                    'controller' => 'list',
                    'action' => 'delete-by',
                ),
                _('Удалить из внешнего кадрового резерва'),
                _('Вы уверены, что хотите удалить выбранных кандидатов внешнего кадрового резерва?')
            );
        }

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    public function assignAction()
    {
        $vacancyId = $this->_getParam('assign_vacancy_id', false);
        if(!$vacancyId){
            $vacancyId = $this->_getParam('vacancy_id', false);
        }
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $reservistsIds = explode(',', $postMassIds);
        $candidateIds  = array();

        foreach ($reservistsIds as $reservistId) {
            $reservist = $this->getService('RecruitReservist')->find($reservistId)->current();
            list($lastName, $firstName, $patronymic) = explode(' ', $reservist->fio);
            $dataArray = array(
                'vacancy_id' => $vacancyId,
                'LastName'   => $lastName,
                'FirstName'  => $firstName,
                'Patronymic' => $patronymic,
                'BirthDate'  => date('Y-m-d', strtotime($reservist->birthday)),
                'EMail'      => $reservist->email,
                'Phone'      => $reservist->phone,
                'Login'      => $reservist->snils,
            );

            $candidate = $this->getService('RecruitCandidate')->createCandidate($dataArray, HM_Recruit_Provider_ProviderModel::ID_EXCEL, HM_Recruit_Provider_ProviderModel::STATUS_ACTUAL);
            $candidateIds[] = $candidate->candidate_id;
        }

        $this->_assign($vacancyId, $candidateIds);
    }

    protected function _assign($vacancyId, $candidateIds, $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE)
    {
        $vacancyService       = $this->getService('RecruitVacancy');
        $vacancyAssignService = $this->getService('RecruitVacancyAssign');
        
        $vacancy = $vacancyService->getOne($vacancyService->findDependence('RecruiterAssign', $vacancyId));
        $vacancyCandidates = $vacancyAssignService->fetchAllDependence(array('Candidate', 'Vacancy'), array(
            'vacancy_id = ?' => $vacancyId
        ));

        $candidateVacancyAssigns = $vacancyAssignService->fetchAll($vacancyAssignService->quoteInto(
            'candidate_id IN (?)',
            $candidateIds
        ))->getList('candidate_id');
        
        $assigned = array();
        foreach ($candidateIds as $candidateId) {
            if($candidateVacancyAssigns[$candidateId]){
                $candidateId = $this->getService('RecruitCandidate')->copyCandidate($candidateId);
            }
            $vacancyCandidate = $this->getService('RecruitVacancyAssign')->assign($vacancyId, $candidateId, $status);

            if ($vacancyCandidate) {
                $vacancyAssignService->assignActive($vacancyCandidate->vacancy_candidate_id);
                $assigned[$candidateId] = $candidateId;
            }
        }
        $diff = array_diff($candidateIds, $assigned);
        if (count($assigned)) {
            $messageType = HM_Notification_NotificationModel::TYPE_SUCCESS;
            $message = !count($diff) ?
                _('Все пользователи успешно включены в список кандидатов на вакансию') :
                _('Часть пользователей включены в список кандидатов на вакансию; не включены: ' . count($diff));
                
        } else {
             $messageType = HM_Notification_NotificationModel::TYPE_ERROR;
             $message = _('Пользователи не включены в список кандидатов');             
        }

        if (!count($vacancyCandidates) && $vacancyCandidate) {
            $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find(intval($vacancy->session_id)));
            $this->getService('RecruitVacancy')->startSession($vacancy, $session);
        }

        $this->_flashMessenger->addMessage(array(
            'type' => $messageType,
            'message' => $message
        ));
        $this->_redirector->gotoUrl($this->view->url(array('module' => 'candidate', 'controller' => 'assign', 'action' => 'index', 'vacancy_id' => $vacancyId)), array('prependBase' => false));
    }

    public function updateFio($reservistId, $fio)
    {
        return '<a href="' . $this->view->url(array('controller' => 'index', 'action' => 'index', 'reservist_id' => $reservistId)) . '">' . $this->view->escape($fio) . '</a>';
    }
}
