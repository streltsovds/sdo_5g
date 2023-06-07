<?php

class Teacher_AssignController extends HM_Controller_Action
{
    protected $_subjectId  = 0;
    /**
     * @var HM_Tc_Subject_SubjectModel
     */
    protected $_subject = null;

    public function init()
    {
        parent::init();

        HM_Teacher_View_ExtendedView::init($this);

        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $requestSources = $request->getParamSources();
        $request->setParamSources(array());

        $subjectId  = (int) $request->getParam('subject_id', 0);

        $request->setParamSources($requestSources);

        if (!$subjectId) {
            throw new Zend_Controller_Router_Exception(_('Курс не найден'), 404);
        }

        $subject = $this->getOne($this->getService('TcSubject')->find($subjectId));

        if (!$subject) {
            throw new Zend_Controller_Router_Exception(_('Курс не найден'), 404);
        }

        $this->_subject = $subject;
        $this->_subjectId  = $subjectId;

    }

    protected function _createForm()
    {
        /** @var HM_Tc_Provider_Teacher_TeacherService $teacherService */
        $teacherService  = $this->getService('TcProviderTeacher');

        $teachers = $teacherService->fetchAll(array(
            'provider_id = ?' => $this->_subject->provider_id
        ));

        $teachers = $teachers->getList('teacher_id', 'name');

        return new HM_Form_TcAssignTeachersToSubject(array(
            'teachers'  => $teachers,
            'subjectId' => $this->_subjectId,
            'cancelUrl' => array(
                'baseUrl'    => 'tc',
                'module'     => 'teacher',
                'controller' => 'list',
                'action'     => 'index'
            )
        ));

    }

    public function indexAction()
    {
        /** @var HM_Tc_Provider_Teacher_Subject_SubjectService $assignService */
        $assignService = $this->getService('TcTeacherSubject');

        $subjectId = $this->_subjectId;

        $form = $this->_createForm();

        $request = $this->getRequest();

        if ($request->isPost()) {

            if ($form->isValid($request->getParams())) {

                $assignService->unAssign(0, 0, $subjectId);

                $teachers = $form->getValue('teachers');

                foreach ($teachers as $teacherId) {
                    $assignService->assign($teacherId, $subjectId);
                }

                $this->_flashMessenger->addMessage(_('Тьюторы успешно назначены на курс'));
                $this->_redirectToIndex();
            }

        } else {

            $assigns = $assignService->fetchAll(array(
                'subject_id = ?' => $subjectId
            ));

            $assigns = $assigns->getList('teacher_id', 'teacher_id');

            $form->setDefault('teachers', $assigns);
        }

        $this->view->form = $form;

    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', 'list', 'teacher', array(
            'subject_id' => $this->_subjectId
        ));
    }
}