<?php
class Infoblock_MyEventsController extends HM_Controller_Action
{
    public function indexAction()
    {
        $eventsDate = strtotime($this->_getParam('events_date', false));

        if (!$eventsDate) {
            $eventsDate = time();
        }

        $this->view->MyEventsBlock(
            null, null, array(
                'eventsDate' => $eventsDate,
                'ajax' => true
            )
        );
    }
}