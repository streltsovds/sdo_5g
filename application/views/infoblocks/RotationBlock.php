<?php


class HM_View_Infoblock_RotationBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'rotation';

    public function rotationBlock($param = null)
    {
        $srv = Zend_Registry::get('serviceContainer');

        $userService = $srv->getService('User');
        $rotationService = $srv->getService('HrRotation');

        $currUser = $userService->getCurrentUser();

        $model = $rotationService->getOne($rotationService->fetchAll(array(
            "user_id = ?" => $currUser->MID,
            "state_id != ?" => HM_Hr_Rotation_RotationModel::PROCESS_STATE_COMPLETE,
            "(result IS NULL OR result = 0)",
        )));


        if (false === $model) return '';


        $this->getService('Process')->initProcess($model);
        $process = $model->getProcess();

        $processStateData = array();
        $state = $this->getService('State')->getOne($this->getService('State')->fetchAllDependence('StateData', array(
            'process_type = ?' => $process->getType(),
            'item_id = ?' => $model->rotation_id,
        )));

        if ($state && count($state->stateData)) {
            foreach ($state->stateData as $item) {
                $processStateData[$item->state] = $item;
            }
        }

        $plan = array(
            'Составление плана ротации' => "заполнить и сдать план ротации до <span>%s</span>",
            'Оценка выполнения плана' => "сдать план ротации с отметками о выполнении задач до <span>%s</span>"
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

                $dateEnd = new DateTime($processStateData[$class]->end_date);

                $events[] = sprintf($plan[$eventTitle], $dateEnd->format('d.m.Y'));
            } else {
                if ($eventTitle == 'Подведение итогов') {
                    $dateEndSession = new DateTime($processStateData[$class]->end_date);
                }
            }
        }

        $interval = $dateEndSession->diff($currDate);
        $show = ($interval->invert > 0 || $interval->invert == 0 && $interval->days == 0);

        // Показываем даже если пусто
//        if (! count($events) || ! $show) return '';

        $this->view->rotation = $model;
        $this->view->events = $events;


    	$content = $this->view->render('rotationBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/rotation/style.css');
//        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/rotation/script.js');

        
        return $this->render($content);
    }
}