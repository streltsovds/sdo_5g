<?php

class HM_Session_View_ExtendedView {

    public static function init(HM_Controller_Action $controller)
    {
        /** @var HM_Controller_Request_Http $request */
        $request = $controller->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $view    = $controller->view;
        $session = $controller->getSession();

        $view->setExtended(
            array(
                'subjectName' => 'TcSession',
                'subjectId' => $session->session_id,
                'subjectIdParamName' => 'session_id',
                'subjectIdFieldName' => 'session_id',
                'subject' => $session
            )
        );
    }

}