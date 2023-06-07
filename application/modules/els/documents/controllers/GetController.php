<?php

use HM_Document_Type_ActivitiesAssessmentModel as Document_ActivitiesAssessment;

class Documents_GetController extends HM_Controller_Action
{

    public function activitiesAssessmentAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        //$this->getHelper('viewRenderer')->setNoRender();

        $sessionId = $this->_getParam('session_id', 0);
        
        $userId = $this->getService('User')->getCurrentUserId();
        
        $this->view->content = $this->view->activitiesAssessmentReport($userId, $sessionId);
    }
         

} 