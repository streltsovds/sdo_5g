<?php
class Session_IndexController extends HM_Controller_Action_Session
{
    public function cardAction()
    {
        $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->findDependence('Cycle', $this->_session->session_id));
        $this->view->session = $session;

        $beginDate = new HM_Date($session->begin_date);
        $endDate = new HM_Date($session->end_date);
        $this->view->lists['generalLeft'] = array(
            _('Наименование должности') => $session->name,
            _('Дата начала') => $beginDate->get(HM_Date::DATE_MEDIUM),
            _('Дата окончания') => $endDate->get(HM_Date::DATE_MEDIUM),
        );
    }



    public function departmentFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 0){
                $select
                    ->joinLeft(array('so1' => 'structure_of_organ'), "so.owner_soid = so1.soid", array())
                    ->where("so1.name LIKE ?", '%' . $value . '%');

        }
    }

    public function changeStateAction()
    {
        $sessionId  = $this->_getParam('session_id',0);
        $state = (int) $this->_getParam('state', 0);

        $session = $this->getService('AtSession')->getOne($this->getService('AtSession')->find($sessionId));
        if ($session && $session->isStateAllowed($state)) {

            switch ($state) {
                case HM_At_Session_SessionModel::STATE_ACTUAL:
                    $this->getService('AtSession')->startSession($sessionId);
                    break;
                case HM_At_Session_SessionModel::STATE_CLOSED:
                    if ($this->getService('Option')->getOption('competenceDisableStop')) {
                        $this->_flashMessenger->addMessage(array(
                            'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => _('Завершение оценочной сессии запрещено в настройках методик оценки'),
                        ));  
                        $this->_redirector->gotoUrl('session/report/card/session_id/' . $sessionId);
                    }
                    $this->getService('AtSession')->stopSession($sessionId);
                    break;
                default:
                    // something wrong..
                    return false;
                    break;
            }
            
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Статус сессии успешно изменен'),
            ));
        }

        $this->_redirector->gotoUrl('session/report/card/session_id/' . $sessionId);
//         $this->_redirector->gotoUrl($this->view->url(array(
//             'module'     => 'session',
//             'controller' => 'index',
//             'action'     => 'card',
//             'session_id' => $sessionId
//          ), null, true));
    }

    public function editAction()
    {
        $form = new HM_Form_Sessions();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $values = $form->getValues();

                $values['begin_date'] = substr($values['begin_date'], 6, 4) . '-' . substr($values['begin_date'], 3, 2) . '-' . substr($values['begin_date'], 0, 2);
                $values['end_date'] = substr($values['end_date'], 6, 4) . '-' . substr($values['end_date'], 3, 2) . '-' . substr($values['end_date'], 0, 2);

                unset($values['checked_items']);
                unset($values['item_type']);

                $res = $this->getService('AtSession')->update($values);

                $this->_flashMessenger->addMessage(_('Оценочная сессия успешно отредактирована'));
                $this->_redirector->gotoUrl('session/report/card/session_id/' . $values['session_id']);
            }
        } else {
            $data = $this->_session->getData();
            $data['begin_date'] = date('d.m.Y', strtotime($data['begin_date']));
            $data['end_date'] = date('d.m.Y', strtotime($data['end_date']));
            $form->populate($data);
        }
        $this->view->form = $form;
    }

    public function controlAction()
    {
    }

    public function resultsAction()
    {
    }

    public function updateKpiAction()
    {

        $sessionCycles = Zend_Registry::get('serviceContainer')->getService('AtSession')->fetchAll()->getList('session_id', 'cycle_id');
        $sessionUsers = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAll(array(
            'kpi_value IS NOT NULL'
        ));

        if (count($sessionUsers)) {
            foreach ($sessionUsers as $sessionUser) {
                $this->_cache['kpi-values'][$sessionUser->session_user_id] = $sessionUser->kpi_value;
            }
        } else {

            $collection = Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->fetchAll(array(
                'status = ?' => HM_At_Session_Event_EventModel::STATUS_COMPLETED,
                'method = ?' => HM_At_Evaluation_EvaluationModel::TYPE_KPI
            ));

            $events = array();
            if (count($collection)) {

                foreach ($collection as $event) {

                    if (empty($sessionCycles[$event->session_id])) continue;

                    $kpiValueWeighted = $criteriaValues = array();
                    $kpis = Zend_Registry::get('serviceContainer')->getService('AtKpiUser')->fetchAllDependence('Kpi', array(
                        'user_id = ?' => $event->user_id,
                        'cycle_id = ?' => $sessionCycles[$event->session_id]
                    ));

                    if (count($kpis)) {
                        foreach ($kpis as $kpiUser) {
                            $kpiValueWeighted[] = $kpiUser->weight * ($kpiUser->value_plan ? $kpiUser->value_fact / $kpiUser->value_plan : 1); // 0 делить на 0 равно 1
                        }
                    }

                    $kpiCriteriaResults = Zend_Registry::get('serviceContainer')->getService('AtEvaluationResults')->fetchAllDependence(array('ScaleValue', 'CriterionKpi'), array('session_event_id = ?' => $event->session_event_id));
                    if (count($kpiCriteriaResults)) {

                        foreach ($kpiCriteriaResults as $result) {
                            if (!count($result->criterionKpi)) continue; // impossible
                            $criterionKpi = $result->criterionKpi->current();
                            $scaleValue = $result->scale_value->current();
                            $criteriaValues[] = $scaleValue->value;
                        }

                        $kpiValue = round(Zend_Registry::get('serviceContainer')->getService('AtKpi')->mapKpiRatio($criteriaValues) * array_sum($kpiValueWeighted) * 100);
                    }

                    Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->updateWhere(array(
                        'kpi_value' => $kpiValue
                    ), array(
                        'session_user_id = ?' => $event->session_user_id
                    ));

                    $this->_cache['kpi-values'][$event->session_user_id] = $kpiValue;
                }
            }
        }
        exit('Success');
    }



    public function getPercentageUser($department, $session_id)
    {
        $select = $this->getService('AtSessionUser')->getSelect();
        $select->from(array('asu' => 'at_session_users'), array(
            'department' => 'so.owner_soid',
            'status' => 'asu.status',
            'user_id'    => 'asu.user_id',
        ));

        $select->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = asu.position_id', array());

        $select->where('asu.session_id = ?', $session_id);
        $select->where('so.owner_soid = ?', $department);
        $select->group(array('so.owner_soid', 'asu.status', 'user_id'));

        $sessionUsers = $select->query()->fetchAll();

        $statusCompletedUsers = 0;
        if (count($sessionUsers)) {
            foreach($sessionUsers as $sessionUser){
                if($sessionUser['status'] == HM_At_Session_User_UserModel::STATUS_COMPLETED) {
                    $statusCompletedUsers++;
                }
            }
            $percentage_user = round($statusCompletedUsers / count($sessionUsers) * 100);
            return $percentage_user;
        }
    }

    public function getAmountUser($department, $session_id)
    {
        $select = $this->getService('AtSessionUser')->getSelect();
        $select->from(array('asu' => 'at_session_users'), array(
            'amount_user' => new Zend_Db_Expr('COUNT(asu.session_user_id)'),
        ));
        $select->join(array('so' => 'structure_of_organ'), 'so.soid = asu.position_id', array());

        $select->where('asu.session_id = ?', $session_id);
        $select->where('so.owner_soid = ?', $department);
        $select->group(array('so.owner_soid'));

        if (count($rowset = $select->query()->fetchAll())) {
            return $rowset[0]['amount_user'];
        }
        return 0;
    }

    public function getPercentageRespondent($department, $session_id)
    {
        $status = HM_At_Session_Event_EventModel::STATUS_COMPLETED;
        $select = $this->getService('AtSessionRespondent')->getSelect();
        $select->from(array('asr' => 'at_session_respondents'), array(
            'department' => 'so.owner_soid',
            'total'    => new Zend_Db_Expr('COUNT(ase.session_event_id)'),
            'passed'    => new Zend_Db_Expr("SUM(CASE WHEN ase.status = '{$status}' THEN 1 ELSE 0 END)"),
        ))
            ->joinLeft(array('so' => 'structure_of_organ'), 'so.soid = asr.position_id', array())
            ->joinLeft(array('ase' => 'at_session_events'), 'ase.session_respondent_id = asr.session_respondent_id', array())
            ->where('asr.session_id = ?', $session_id)
            ->where('so.owner_soid = ?', $department)
            ->group(array('so.owner_soid'));

        $rowset = $select->query()->fetchAll();
        if (count($rowset = $select->query()->fetchAll())) {
            return $rowset[0]['total'] ? round(100 * $rowset[0]['passed']/$rowset[0]['total']) : 0;
        }
    }

    public function getAmountEvent($department, $session_id)
    {
        $select = $this->getService('AtSessionEvent')->getSelect();
        $select->from(array('ase' => 'at_session_events'), array(
            'amount_event' => new Zend_Db_Expr('COUNT(ase.session_event_id)'),
        ));
        $select->join(array('asr' => 'at_session_respondents'), 'ase.session_respondent_id = asr.session_respondent_id', array());
        $select->join(array('so' => 'structure_of_organ'), 'so.soid = asr.position_id', array());
        
        $select->where('asr.session_id = ?', $session_id);
        $select->where('so.owner_soid = ?', $department);
        $select->group(array('so.owner_soid'));

        $rowset = $select->query()->fetchAll();
        if (count($rowset = $select->query()->fetchAll())) {
            return $rowset[0]['amount_event'];
        }
        return 0;
    }
}
