<?php

class HM_Subject_View_FeedbackExtendedView {

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
        $request->setParamSources($requestSources);

        if ($controller->isProviderCase()) {
            /** @var HM_Tc_Provider_ProviderService $providerService */
            $providerService = $controller->getService('TcProvider');

            $provider = $providerService->getOne($providerService->find($controller->getProviderId()));

            if ($provider) {
                $view->setExtended(array(
                    'subjectName'        => 'TcProvider',
                    'subjectId'          => $controller->getProviderId(),
                    'subjectIdParamName' => 'provider_id',
                    'subjectIdFieldName' => 'provider_id',
                    'subject'            => $provider
                ));
            }
        } elseif ($controller->getSubjectId()) {
            $subjectService = $controller->getService('Subject');
            $subject = $subjectService->getOne($subjectService->find($controller->getSubjectId()));

            $view->setExtended(
                array(
                    'subjectName'        => 'Fulltime',
                    'subjectId'          => $controller->getSubjectId() ,
                    'subjectIdParamName' => 'subject_id',
                    'subjectIdFieldName' => 'subid',
                    'subject'            => $subject,

                    'extraSubjectIdParamName' => 'base_id',
                    'extraSubjectId'          => $controller->getSubjectId(),
                )
            );
        }
    }

}