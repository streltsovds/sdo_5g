<?php

class Eclass_IndexController extends HM_Controller_Action {

    CONST BASE_URL = '/site/auth/external/';

    public function indexAction()
    {
        $lessonId = $this->_getParam('lesson_id');
        $subjectId = $this->_getParam('subject_id');

        $data = $this->getService('Eclass')->getWebinarVideo($lessonId);
        if ($data->isFinished && $this->getService('User')->isEnduser()){
            $this->_redirector->gotoSimple('index', 'video', 'eclass', array('lesson_id' => $lessonId, 'subject_id' => $subjectId));
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $webinar_event_id = $this->getService('Lesson')->find($lessonId)->current()->webinar_event_id;
        
        $config = Zend_Registry::get('config');
        $webinarHost = $config->eclass->webinarHost;
        $appKey      = $config->eclass->appKey;
        
        $eclassService = $this->getService('Eclass');
        $userService     = $this->getService('User');
        
        $currentUserId = $userService->getCurrentUserId(); 
        
        $data = array(
            'user_id'  => $currentUserId, 
            'event_id' => $webinar_event_id,
            'app_key'  => $appKey
        );
        
        $sign = $eclassService->generateSign($data);
        
        $this->view->webinarUrl =
            $webinarHost .
            self::BASE_URL .
            "?user_id={$currentUserId}" .
            "&event_id={$webinar_event_id}" .
            "&app_key={$appKey}" .
            "&sign={$sign}"
        ;
    }
}