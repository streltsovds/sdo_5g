<?php
class HM_View_Helper_EventPreview extends HM_View_Helper_Abstract
{
    public function eventPreview($event)
    {
        $this->view->currentUserId = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $this->view->event = $event;
        return $this->view->render('event-preview.tpl');
    }
}