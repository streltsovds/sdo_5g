<?php

class HM_SessionQuarter_View_ExtendedView {

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
                'subjectName' => 'TcSessionQuarter',
                'subjectId' => $session->session_quarter_id,
                'subjectIdParamName' => 'session_quarter_id',
                'subjectIdFieldName' => 'session_quarter_id',
                'subject' => $session
            )
        );
    }

}