<?php

class HM_Provider_View_ExtendedView {

    public static function init(HM_Controller_Action $controller)
    {
        /** @var HM_Controller_Request_Http $request */
        $request = $controller->getRequest();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $view = $controller->view;

        $requestSources = $request->getParamSources();
        $request->setParamSources(array());

        $providerId = $request->getParam('provider_id', 0);
        $sessionId  = $request->getParam('session_id', 0);

        $request->setParamSources($requestSources);

        if ($sessionId) {

            $providerService = $controller->getService('TcSession');

            $session = $providerService->getOne(
                $providerService->find($sessionId)
            );

            $view->setExtended(
                array(
                    'subjectName' => 'TcSession',
                    'subjectId' => $sessionId,
                    'subjectIdParamName' => 'session_id',
                    'subjectIdFieldName' => 'session_id',
                    'subject' => $session
                )
            );

        } else {

            $providerService = $controller->getService('TcProvider');

            $provider = $providerService->getOne(
                $providerService->find($providerId)
            );

            $view->setExtended(
                array(
                    'subjectName' => $provider->type == HM_Tc_Provider_ProviderModel::TYPE_STUDY_CENTER
                        ? 'TcStudyCenter'
                        : 'TcProvider',
                    'subjectId' => $providerId,
                    'subjectIdParamName' => 'provider_id',
                    'subjectIdFieldName' => 'provider_id',
                    'subject' => $provider
                )
            );
        }
    }

}