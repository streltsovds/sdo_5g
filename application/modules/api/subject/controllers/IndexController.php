<?php

class Subject_IndexController extends HM_Controller_Action_RestOauth
{
    /** @var HM_Subject_SubjectService _defaultService */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('Subject');
    }

    public function getNotFoundMessage()
    {
        return 'Subject not found';
    }

    public function assignAction()
    {
        $subjectId = $this->_getParam('id');
        $userId = $this->_getParam('action_id');

        if (!is_numeric($subjectId) || !is_numeric($userId)) {
            $this->setStatusBadRequest();
            return false;
        }

        if ($userId > 0 && $subjectId > 0) {

            /** @var HM_Role_StudentService $service */
            $service = $this->getService('Student');

            if (!$service->isUserExists($subjectId, $userId)) {
                $service->insert(['CID' => $subjectId, 'MID' => $userId]);
                $this->setStatusSuccessfulCreated();
            } else {
                // Уже есть такое назначение, какую ошибку выдать?
                $this->setStatusInvalidInput();
            }
        } else {
            $this->setStatusInvalidInput();
        }
    }

    public function applyAction()
    {
        $subjectId = $this->_getParam('id');
        $userId = $this->_getParam('action_id');

        if (!is_numeric($subjectId) || !is_numeric($userId)) {
            $this->setStatusBadRequest();
            return false;
        }

        if ($userId > 0 && $subjectId > 0) {

            try {
                $this->_defaultService->assignClaimant($subjectId, $userId);
                $this->setStatusSuccessfulCreated();
            } catch (Exception $e) {
                $this->setStatusInvalidInput();
            }

        } else {
            $this->setStatusInvalidInput();
        }
    }
}