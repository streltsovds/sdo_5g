<?php

class Session_NewProvidersController extends HM_Controller_Action
{
    protected $_sessionId = 0;
    protected $_session = null;

    public function init()
    {
        /** @var HM_Tc_Session_SessionService $sessionService */
        $sessionService = $this->getService('TcSession');

        $this->_sessionId  = (int) $this->_getParam('session_id', 0);
        $this->_session = $this->getOne(
            $sessionService->find($this->_sessionId)
        );

        parent::init();

        HM_Session_View_ExtendedView::init($this);
    }

    public function indexAction()
    {
        $applicationsStatus = $this->getService('TcSession')->applicationsStatus($this->_sessionId);
        if ($applicationsStatus != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            $this->view->setSubHeader(HM_Tc_Session_SessionModel::getApplicationsStateMessage($applicationsStatus));
        }

        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = $this->getService('TcProvider');

        $providers = $providerService
            ->fetchAll($this->quoteInto('status = ?', 1))
            ->getList('provider_id', 'name');

        $grid = HM_Session_Grid_NewProvidersGrid::create(array(
            'session_id' => $this->_sessionId,
            'providers'  => $providers
        ));

        $listSource = $providerService->getListOfNewProvidersSource($this->_sessionId);

        $this->view->assign(array(
            'grid' => $grid->init($listSource)
        ));
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', null, null, array(
            'session_id' => $this->_sessionId
        ));
    }

    public function concatenationAction()
    {
        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = $this->getService('TcProvider');

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $targetProviderId = $this->_getParam('target_provider_id', 0);

        $targetProvider = $this->getOne($providerService->find($targetProviderId));

        if (!$targetProvider) {

            $message = array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Провайдер не найден')
            );

        } else {

            $ids = explode(',', $postMassIds);

            if (count($ids)) {
                $providerService->concatenate($targetProviderId, $ids);
            }

            $message = array(
                'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Провайдеры успешно объеденены с провайдером "'.$targetProvider->name.'"')
            );

        }

        $this->_flashMessenger->addMessage($message);

        $this->_redirectToIndex();

    }

    public function getSession()
    {
        return $this->_session;
    }

} 