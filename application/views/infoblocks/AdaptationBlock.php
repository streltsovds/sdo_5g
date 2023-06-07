<?php


class HM_View_Infoblock_AdaptationBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'adaptation';

    public function adaptationBlock($param = null)
    {
        $srv = Zend_Registry::get('serviceContainer');

        $userService = $srv->getService('User');
        $newcomerService = $srv->getService('RecruitNewcomer');

        $currUser = $userService->getCurrentUser();

        $model = $newcomerService->getOne($newcomerService->fetchAll(array(
            "user_id = ?" => $currUser->MID,
            "state != ?" => HM_Recruit_Newcomer_NewcomerModel::PROCESS_STATE_COMPLETE,
            "(result IS NULL OR result = 0)",
        )));


        if (false === $model) return '';


        $this->getService('Process')->initProcess($model);
        $process = $model->getProcess();

        $processStateData = array();
        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $model->newcomer_id,
        )));

        if ($state && count($state->stateData)) {
            foreach ($state->stateData as $item) {
                $processStateData[$item->state] = $item;
            }
        }

        $plan = array(
            'Составление плана адаптации' => "заполнить и сдать план адаптации до <span>%s</span>",
            'Оценка выполнения плана' => "сдать план адаптации с отметками о выполнении задач до <span>%s</span>"
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
                // ай-ай как нехорошо
                if ($eventTitle == 'Подведение итогов') {
                    $dateEndSession = new DateTime($processStateData[$class]->end_date_planned);
                }
            }
        }

        $interval = $dateEndSession->diff($currDate);
        $show = ($interval->invert > 0 || $interval->invert == 0 && $interval->days == 0);

        if (! count($events) || ! $show) return '';

        $this->view->newcomer = $model;
        $this->view->events = $events;


    	$content = $this->view->render('adaptationBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/adaptation/style.css');
//        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/adaptation/script.js');

        
        return $this->render($content);
    }
}