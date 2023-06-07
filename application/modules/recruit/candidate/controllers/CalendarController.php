<?php
class Candidate_CalendarController extends HM_Controller_Action_Vacancy
{
    public function init()
    {
        parent::init();

        if ($vacancyCandidateId = $this->_getParam('vacancy_vacancyCandidate_id', 0)) {

            $this->_vacancyCandidate = $this->getService('RecruitVacancyAssign')->getOne($this->getService('RecruitVacancyAssign')->findDependence(array('User', 'Cycle'), $vacancyCandidateId));
            if (count($this->_vacancyCandidate->user)) {
                $this->_user = $this->_vacancyCandidate->user->current();
            }
        }
    }

    public function indexAction()
    {
	    $isEditable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                ));

        if ($this->_getParam('start',0) && $this->_getParam('end',0)) {

            $vacancyCandidateId = $this->_getParam('vacancy_candidate_id', 0);
            $model =  $this->getService('RecruitVacancyAssign')->find($vacancyCandidateId)->current();
            $this->getService('Process')->initProcess($model);
            $process = $model->getProcess();
            $processAbstract = $process->getProcessAbstract();

            $stateData = array();
            $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
                'process_type = ?' => $process->getType(),
                'item_id = ?' => $vacancyCandidateId,
            )));

            if ($state && count($state->stateData)) {

                foreach ($state->stateData as $item) {
                    $stateData[$item->state] = $item;
                }

                $eventsSources = array();
                foreach ($process->getStates() as $key => $state) {
                    $class = $processAbstract->getStateClass($state);
                    if (!isset($stateData[$class])) continue;

                    $title = $state->getTitle();
                    $color = substr(md5($title), 0, 6);
                    $eventsSources[] = array(
                        'id'    => $stateData[$class]->state_of_process_data_id,
                        'title' => $title,
                        'start' => $stateData[$class]->begin_date, // отображаем фактические даты;
                        'end'   => $stateData[$class]->end_date, // пока мероприятие не пройдено, они совпадают с плановыми
                        'editable' => $isEditable,
                        'color' => "#{$color}",
                        'borderColor' => "#{$color}",
                        'url' => $this->view->url(array(
                            'module' => 'candidate',
                            'controller' => 'index',
                            'action' => 'resume',
                            'vacancy_id' => $model->vacancy_id,
                            'candidate_id' => $model->candidate_id,
                            'no_user_events' => null,
                        )),

                    );
                }
            }
            $tempView = $this->view->assign($eventsSources);
            unset($tempView->lists);

        } else {
            $this->view->source = array('module'=>'candidate', 'controller'=>'calendar', 'action'=>'index', 'no_user_events' => 'y');
            $this->view->editable = $isEditable;
        }        
    }

    public function allAction()
    {
	    $isEditable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                ));

        if ($this->_getParam('start',0) && $this->_getParam('end',0)) {

            $vacancyId = $this->_getParam('vacancy_id', 0);
            $collection =  $this->getService('RecruitVacancyAssign')->fetchAllDependence('User', array(
                'vacancy_id = ?' => $vacancyId,
                'status != ?' => HM_Recruit_Vacancy_Assign_AssignModel::STATUS_HOLD_ON,
            ));

            $eventsSources = array();
            foreach ($collection as $vacancyCandidate) {

                $this->getService('Process')->initProcess($vacancyCandidate);
                $process = $vacancyCandidate->getProcess();
                $processAbstract = $process->getProcessAbstract();

                $userName = $vacancyCandidate->user ? $vacancyCandidate->user->current()->getName() : '';

                $stateData = array();
                $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
                    'process_type = ?' => $process->getType(),
                    'item_id = ?' => $vacancyCandidate->vacancy_candidate_id,
                )));

                if ($state && count($state->stateData)) {

                    foreach ($state->stateData as $item) {
                        $stateData[$item->state] = $item;
                    }

                    foreach ($process->getStates() as $key => $state) {
                        $class = $processAbstract->getStateClass($state);
                        if (!isset($stateData[$class])) continue;

                        $title = sprintf('%s: %s', $userName, $state->getTitle());
                        $color = substr(md5($state->getTitle()), 0, 6);
                        $eventsSources[] = array(
                            'id' => $stateData[$class]->state_of_process_data_id,
                            'title' => $title,
                            'start' => $stateData[$class]->begin_date, // отображаем фактические даты;
                            'end'   => $stateData[$class]->end_date, // пока мероприятие не пройдено, они совпадают с плановыми
                            'editable' => $isEditable,
                            'color' => "#{$color}",
                            'borderColor' => "#{$color}",
                            'url' => $this->view->url(array(
                                'module' => 'candidate',
                                'controller' => 'index',
                                'action' => 'resume',
                                'vacancy_id' => $vacancyId,
                                'candidate_id' => $vacancyCandidate->candidate_id,
                                'no_user_events' => null,
                            )),
                        );
                    }
                }
                $tempView = $this->view->assign($eventsSources);
                unset($tempView->lists);
            }
        } else {
            $this->view->source = array('module'=>'candidate', 'controller'=>'calendar', 'action'=>'all', 'no_user_events' => 'y');
            $this->view->editable = $isEditable;
        }
    }

    public function saveCalendarAction()
    {
        $stateOfProcessDataId = $this->_getParam('eventid',0);
        $begin = date('Y-m-d H:i:s', (floatval($this->_getParam('start'))/1000)); //в миллисекундах
        $end   = date('Y-m-d H:i:s', ($endSeconds = floatval($this->_getParam('end'))/1000));

        $result    = _('При сохранении данных произошла ошибка');
        $status    = 'fail';

        if ($this->_request->isPost() && $stateOfProcessDataId && $begin && $end) {
            $stateData = $this->getService('StateData')->findDependence('State', $stateOfProcessDataId)->current();
            if ($stateData) {

                $data = $stateData->getData();
                $data['begin_date_planned'] = $data['begin_date'] = $begin;
                $data['end_date_planned'] = $data['end_date'] = $endSeconds ? $end : $begin;

                $stateData = $this->getService('StateData')->update($data);

// нет нужды править даты event'ов
//                $vacancyCandidate = $this->getService('RecruitVacancyAssign')->find($vacancyCandidateId)->current();
//                $vacancy          = $this->getService('RecruitVacancy')->find($vacancyId)->current();
//                $sessionEvent     = $this->getService('AtSessionEvent')->getOne(
//                    $this->getService('AtSessionEvent')->fetchAll(
//                        array(
//                            'session_id    = ?' => $vacancy->session_id,
//                            'user_id       = ?' => $vacancyCandidate->user_id,
//                            'respondent_id = ?' => $this->getService('User')->getCurrentUserId()
//                        )
//                    )
//                );
//                $atSessionEvent = $this->getService('AtSessionEvent')->update(
//                    array(
//                        'session_event_id' => $sessionEvent->session_event_id,
//                        'date_begin' => $begin,
//                        'date_end' => $end
//                    )
//                );
                if ($stateData) {
                    $result = _('Данные успешно сохранены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }
}