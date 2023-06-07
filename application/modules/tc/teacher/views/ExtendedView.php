<?php

class HM_Teacher_View_ExtendedView {

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
        $subjectId  = $request->getParam('subject_id', 0);

        $request->setParamSources($requestSources);

        if ($providerId) {

            /** @var HM_Tc_Provider_ProviderService $providerService */
            $providerService = $controller->getService('TcProvider');

            $provider = $providerService->getOne($providerService->find($providerId));

            if ($provider) {
                $view->setExtended(array(
                    'subjectName'        => 'TcProvider',
                    'subjectId'          => $providerId,
                    'subjectIdParamName' => 'provider_id',
                    'subjectIdFieldName' => 'provider_id',
                    'subject'            => $provider
                ));
            }

        } elseif ($subjectId) {

            /** @var HM_Subject_SubjectService $providerService */
            $subjectService = $controller->getService('Subject');

            $subject = $subjectService->getOne($subjectService->find($subjectId));

            if ($subject) {
                $view->setExtended(array(
                    'subjectName'        => 'Fulltime',
                    'subjectId'          => $subjectId,
                    'subjectIdParamName' => 'subject_id',
                    'subjectIdFieldName' => 'subid',
                    'subject'            => $subject,

                    'extraSubjectIdParamName' => 'base_id',
                    'extraSubjectId'          => $subjectId,
                ));
            }

        }
    }

}