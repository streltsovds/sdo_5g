<?php

class User_IndexController extends HM_Controller_Action_RestOauth
{

    /** @var HM_User_UserService _defaultService */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('User');
    }

    public function getNotFoundMessage()
    {
        return 'User not found';
    }

    public function subjectAssignmentsAction()
    {
        $id = $this->_getParam('id');

        if (!is_numeric($id)) {
            $this->setStatusBadRequest();
            return false;
        }

        if ($id > 0) {

            /** @var HM_Subject_User_UserService $service */
            $service = $this->getService('SubjectUser');
            $collection = $service->fetchAll(['subject_id > 0', 'user_id = ?' => $id]);

            if ($collection) {
                $result = $collection->getList('subject_id', 'getRestDefinition');
                $this->view->assign(array_values($result));
            } else {
                $this->setStatusNotFound();
            }
        }
    }

    public function lessonAssignmentsAction()
    {
        $id = $this->_getParam('id');

        if (!is_numeric($id)) {
            $this->setStatusBadRequest();
            return false;
        }

        if ($id > 0) {

            /** @var HM_Lesson_Assign_AssignService $service */
            $service = $this->getService('LessonAssign');
            $collection = $service->fetchAllDependence(['Lesson'], ['SHEID > 0', 'MID = ?' => $id]);

            if ($collection) {
                $result = $collection->getList('SHEID', 'getRestDefinition');
                $this->view->assign(array_values($result));
            } else {
                $this->setStatusNotFound();
            }
        } else {
            $this->setStatusInvalidInput();
        }
    }
}