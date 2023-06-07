<?php


class HM_View_Infoblock_ReserveBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'reserve';

    public function reserveBlock($param = null)
    {
        $srv = Zend_Registry::get('serviceContainer');

        $userService = $srv->getService('User');
        $reserveService = $srv->getService('HrReserve');

        $currUser = $userService->getCurrentUser();

        $models = $reserveService->fetchAll(array(
            "user_id = ?" => $currUser->MID,
            "state_id != ?" => HM_Hr_Reserve_ReserveModel::PROCESS_STATE_COMPLETE,
            "(result IS NULL OR result = 0)",
        ));

        $modelsArray =
        $eventsArray = array();
        foreach ($models as $model) {
            if (false === $model) continue;
            $srv->getService('Process')->initProcess($model);
            $process = $model->getProcess();

            $processStateData = array();
            $state = $srv->getService('State')->getOne($srv->getService('State')->fetchAllDependence('StateData', array(
                'process_type = ?' => $process->getType(),
                'item_id = ?' => $model->reserve_id,
            )));

            if ($state && count($state->stateData)) {
                foreach ($state->stateData as $item) {
                    $processStateData[$item->state] = $item;
                }
            }

            $plan = array(
                'Составление ИПР' => "заполнить и сдать план ИПР до <span>%s</span>",
                'Оценка выполнения плана' => "сдать план ИПР с отметками о выполнении задач до <span>%s</span>"
            );

//        $eventsSources = array();
            $events = array();
            $currDate = new DateTime();
            $dateEndSession = clone $currDate;

            foreach ($process->getStates() as $key => $state) {
                $class = get_class($state);
                if (!isset($processStateData[$class])) continue;
                $eventTitle = $state->getTitle();

                if (isset($plan[$eventTitle])) {
                    $dateEnd = new DateTime($processStateData[$class]->end_date_planned);

                    $events[] = sprintf($plan[$eventTitle], $dateEnd->format('d.m.Y'));
                } else {
                    if ($eventTitle == 'Подведение итогов') {
                        $dateEndSession = new DateTime($processStateData[$class]->end_date_planned);
                    }
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

    	$content = $this->view->render('reserveBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/reserve/style.css');
//        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/reserve/script.js');

        
        return $this->render($content);
    }
}