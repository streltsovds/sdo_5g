<?php
class HM_Controller_Plugin_Poll extends Zend_Controller_Plugin_Abstract
{
    //trash
    /*public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $service      = Zend_Registry::get('serviceContainer')->getService('PollLink');
        $pollLinks    = $service->getCurrentLinks();
        $pollLinksIds = (count($pollLinks))? $pollLinks->getList('link_id') : array();

        if ( count($pollLinksIds) ) {
            // Хардкод: заточка под то, что к одной странице привязывается один опрос.

            $pollLinks->rewind();
            $link        = $pollLinks->current();
            $currentPoll = ($link)? $link->quiz_id : 0;

            $response = $this->getResponse();
            $status   = ($service->getService('Poll')->canViewResultPageRate($currentPoll))? 'RATED' : 'UNRATED';
            $content  = Zend_Registry::get('view')->pageRate($status, $pollLinksIds, $currentPoll);
            $response->appendBody($content);
        }

    }*/
}
