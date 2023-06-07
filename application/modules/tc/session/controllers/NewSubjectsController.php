<?php

class Session_NewSubjectsController extends HM_Controller_Action
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

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)

            ->addActionContext('find-subject-for-concatenation', 'json')

            ->initContext('json');

    }

    public function indexAction()
    {
        $applicationsStatus = $this->getService('TcSession')->applicationsStatus($this->_sessionId);
        if ($applicationsStatus != HM_Tc_Session_SessionModel::STATE_ACTUAL) {
            $this->view->setSubHeader(HM_Tc_Session_SessionModel::getApplicationsStateMessage($applicationsStatus));
        }

        /** @var HM_Tc_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('TcSubject');

        $grid = HM_Session_Grid_NewSubjectsGrid::create(array(
            'session_id' => $this->_sessionId,
        ));

        $listSource = $subjectService->getListOfNewSubjectsSource($this->_sessionId);

        $this->view->assign(array(
            'grid' => $grid->init($listSource)
    ));
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function findSubjectForConcatenationAction()
    {
        $search = $this->_getParam('tag', '');

        $result = array();

        if ($search) {

            /** @var HM_Tc_Subject_SubjectService $subjectService */
            $subjectService = $this->getService('TcSubject');

            $result = $subjectService->findSubjectsForAutoComplete($search, 20, true);

        }

        $this->view->assign($result);

    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', null, null, array(
            'session_id' => $this->_sessionId
        ));
    }

    public function concatenationAction()
    {
        /** @var HM_Tc_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('TcSubject');

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $targetSubjectId = $this->_getParam('subject_id', array());

        $targetSubject = $this->getOne($subjectService->find($targetSubjectId));

        if (!$targetSubject) {

            $message = array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Курс не найден')
            );

        } else {

            $ids = explode(',', $postMassIds);

            if (count($ids)) {
                $subjectService->concatenate($targetSubject, $ids);
            }

            $message = array(
                'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
                'message' => _('Курсы успешно объеденены с курсом "'.$targetSubject->name.'"')
            );

        }

        $this->_flashMessenger->addMessage($message);

        $this->_redirectToIndex();

    }

} 