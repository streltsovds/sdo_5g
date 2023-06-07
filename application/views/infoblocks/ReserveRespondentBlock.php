<?php


class HM_View_Infoblock_ReserveRespondentBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'reserverespondent';

    public function reserveRespondentBlock($param = null)
    {
        $modelsArray   =
        $eventsArray   =
        $respondentIds = array();

        $srv = Zend_Registry::get('serviceContainer');
        $userService = $srv->getService('User');
        $reserveService = $srv->getService('HrReserve');
        $currUser = $userService->getCurrentUser();

        $select = $srv->getService('HrReserve')->getSelect();
        $select->from(array('r' => 'hr_reserves'),
            array(
                'r.reserve_id',
                'rp.custom_respondents'
            ))
        ->joinInner(array('rp' => 'hr_reserve_positions'), 'rp.reserve_position_id=r.reserve_position_id', array())
        ->where("r.user_id != ?", $currUser->MID)
        ->where("r.state_id != ?" , HM_Hr_Reserve_ReserveModel::PROCESS_STATE_COMPLETE)
        ->where("(result IS NULL OR result = ?) AND rp.custom_respondents IS NOT NULL" , 0);

        $rows = $select->query()->fetchAll();

        foreach ($rows as $row) {
            $ids = unserialize($row['custom_respondents']);
            $ids = is_array($ids) ? $ids : array($ids);
            foreach ($ids as $id) {
                $respondentIds[$id][] = $row['reserve_id'];
            }
        }

        $models = $reserveService->fetchAll(array(
            "reserve_id IN (?)" => empty($respondentIds[$currUser->MID]) ? array(0) : $respondentIds[$currUser->MID]
        ));

        foreach ($models as $model) {
            if (false === $model) continue;
            $srv->getService('Process')->initProcess($model);
            $process = $model->getProcess();

            $processStateData = array();
            $state = $srv->getService('State')->getOne($srv->getService('State')->fetchAllDependence('StateData', array(
                'process_type = ?' => $process->getType(),
                'item_id = ?' => $model->reserve_id,
                'status IN (?)' => array(HM_Process_Abstract::PROCESS_STATUS_INIT, HM_Process_Abstract::PROCESS_STATUS_CONTINUING)
            )));

            if ($state && count($state->stateData)) {
                foreach ($state->stateData as $item) {
                    if ($item->state == 'HM_Hr_Reserve_State_Publish') $processStateData[$item->state] = $item;
                }
            }

            $plan = array(
                'Оценка выполнения плана' => "оценить сотрудника <span>%s</span> до <span>%s</span>"
            );

            $events = array();
            $currDate = new DateTime();
            $dateEndSession = clone $currDate;

            foreach ($process->getStates() as $key => $state) {
                $class = get_class($state);
                if (!isset($processStateData[$class])) continue;
                $eventTitle = $state->getTitle();

                if (isset($plan[$eventTitle])) {
                    $dateEnd = new DateTime($processStateData[$class]->end_date);
                    $fio = $srv->getService('User')->find($model->user_id)->current()->getName();
                    $events[] = sprintf($plan[$eventTitle], $fio, $dateEnd->format('d.m.Y'));
                }
            }

            $interval = $dateEndSession->diff($currDate);
            $show = ($interval->invert > 0 || $interval->invert == 0 && $interval->days == 0);

            if (! count($events) || ! $show) continue;

            $modelsArray[$model->reserve_id]   = $model;
            $eventsArray[$model->reserve_id][] = $events;
        }

        if (empty($modelsArray)) return false;

        $this->view->reserves = $modelsArray;
        $this->view->events   = $eventsArray;

        $content = $this->view->render('reserveRespondentBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/reserve/style.css');
//        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/reserve/script.js');

        
        return $this->render($content);
    }
}