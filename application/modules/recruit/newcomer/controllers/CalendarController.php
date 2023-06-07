<?php
class Newcomer_CalendarController extends HM_Controller_Action_Newcomer
{
    public function indexAction()
    {
	    $isEditable = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                    HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
                ));

        if ( $this->_getParam('start',0) && $this->_getParam('end',0)) {

            $newcomerId = $this->_getParam('newcomer_id', 0);
            $model =  $this->getService('RecruitNewcomer')->find($newcomerId)->current();
            $this->getService('Process')->initProcess($model);
            $process = $model->getProcess();

            $stateData = array();
            $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
                'process_type = ?' => $process->getType(),
                'item_id = ?' => $newcomerId,
            )));

            if ($state && count($state->stateData)) {

                foreach ($state->stateData as $item) {
                    $stateData[$item->state] = $item;
                }

                $eventsSources = array();
                foreach ($process->getStates() as $key => $state) {
                    $class = get_class($state);
                    if (!isset($stateData[$class])) continue;
                    $eventsSources[] = array(
                        'id'    => $stateData[$class]->state_of_process_data_id,
                        'title' => $state->getTitle(),
                        'start' => $stateData[$class]->begin_date_planned,
                        'end'   => $stateData[$class]->end_date_planned,
                        'editable' => $isEditable,
                        'color' => HM_Recruit_Newcomer_NewcomerModel::getStateColor($class),
                        'borderColor' => HM_Recruit_Newcomer_NewcomerModel::getStateColor($class),
                    );
                }
            }
            $tempView = $this->view->assign($eventsSources);
            unset($tempView->lists);

        } else {
            $this->view->source = array('module'=>'newcomer', 'controller'=>'calendar', 'action'=>'index', 'no_user_events' => 'y');
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
            $state = $this->getService('StateData')->find($stateOfProcessDataId)->current();
            if ($state) {
                $data = $state->getData();
                $data['begin_date_planned'] = $begin;
                $data['end_date_planned'] = $endSeconds ? $end : $begin;
                if ($this->getService('StateData')->update($data)) {
                    $result = _('Данные успешно сохранены');
                    $status = 'success';
                }
            }
        }
        $this->view->status = $status;
        $this->view->msg    = $result;
    }
}