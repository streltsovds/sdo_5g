<?php

class Teacher_EditController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    /** @var HM_Tc_Provider_Teacher_TeacherService */
    protected $_teacherService = null;
    /** @var HM_Tc_Provider_ProviderService */
    protected $_providerService = null;
    /** @var HM_Subject_SubjectService */
    protected $_subjectService = null;

    protected $_teacherId  = 0;
    protected $_providerId = 0;
    protected $_subjectId  = 0;

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Тьютор успешно создан'),
            self::ACTION_UPDATE    => _('Тьютор успешно обновлён'),
            self::ACTION_DELETE    => _('Тьютор успешно удалён'),
            self::ACTION_DELETE_BY => _('Тьюторы успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Тьютор не был создан'),
            self::ERROR_NOT_FOUND        => _('Тьютор не найден')
        );
    }

    public function init()
    {
        parent::init();

        HM_Teacher_View_ExtendedView::init($this);

        $this->_teacherService  = $this->getService('TcProviderTeacher');
        $this->_subjectService  = $this->getService('TcSubject');
        $this->_providerService = $this->getService('TcProvider');

        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $requestSources = $request->getParamSources();
        $request->setParamSources(array());

        $this->_teacherId  = (int) $request->getParam('teacher_id', 0);
        $this->_subjectId  = (int) $request->getParam('subject_id', 0);
        $this->_providerId = (int) $request->getParam('provider_id', 0);

        $request->setParamSources($requestSources);

        $this->_initForm();

        $currentRole = $this->getService('User')->getCurrentUserRole();
        $actionName = $this->getRequest()->getActionName();
        if (in_array($actionName, array ('delete', 'edit')) && $this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $teacher = $this->_teacherService->getOne($this->_teacherService->find($this->_teacherId));
            if ($teacher->created_by != $this->getService('User')->getCurrentUserId()) {
                $this->_redirectToIndex();
            }
        }

    }

    protected function _initForm()
    {
        $subjectId  = $this->_subjectId;
        $providerId = $this->_providerId;
        $teacherId  = $this->_teacherId;

        $formConfig = array(
            'subjectId'  => $subjectId,
            'providerId' => $providerId,
            'teacherId'  => $this->_teacherId,
            'cancelUrl' => array(
                'baseUrl'    => 'tc',
                'module'     => 'teacher',
                'controller' => 'list',
                'action'     => 'index'
            )
        );

        if (!$providerId && !$subjectId && !$teacherId) {

            $providers = $this->_providerService->fetchAll(
                $this->quoteInto('type=?', HM_Tc_Provider_ProviderModel::TYPE_PROVIDER));

            $formConfig['providers'] = $providers->getList('provider_id', 'name');

        }

        if ($providerId) {
            $subjects = $this->_subjectService->fetchAll(array(
                'provider_id = ?' => $providerId,
                ' (base IS NULL OR base <> ?)' => HM_Subject_SubjectModel::BASETYPE_SESSION
            ));

            $formConfig['subjects'] = $subjects->getList('subid', 'name');

        }

        $this->_setForm(new HM_Form_TcTeacher($formConfig));

    }

    protected function _redirectToIndex()
    {
        $params = array();

        if ($this->_providerId) {
            $params['provider_id'] = $this->_providerId;
        }

        if ($this->_subjectId) {
            $params['subject_id'] = $this->_subjectId;
        }

        $this->_redirector->gotoSimple('index', 'list', 'teacher', $params);
    }

    public function setDefaults(HM_Form_TcTeacher $form)
    {
        /** @var HM_Tc_Provider_Teacher_Subject_SubjectService $assignService */
        $assignService = $this->getService('TcTeacherSubject');
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        $teacherId = $this->_teacherId;

        $teacher = $this->_teacherService->find($teacherId);

        $teacher = $this->getOne($teacher);

        if (!$teacher) {
            return;
        }

        $values = $teacher->getValues();

        if ($form->getElement('subjects')) {
            $subjectAssign = $assignService->fetchAll(array(
                'teacher_id = ?' => $teacherId,
                'provider_id = ?' => $this->_providerId
            ));

            $values['subjects'] = $subjectAssign->getList('subject_id', 'subject_id');
        }

        $values['files'] = $fileService->getItemFiles(
            HM_Files_FilesModel::ITEM_TYPE_TC_TEACHER,
            $teacherId
        );

        $form->populate($values);

    }

    public function create(HM_Form_TcTeacher $form)
    {
        /** @var HM_Tc_Provider_Teacher_Subject_SubjectService $assignService */
        $assignService = $this->getService('TcTeacherSubject');

        $values = $form->getValues();

        $subjects = array();

        if ($this->isProviderContext()) {
            $values['provider_id'] = $this->_providerId;
            $subjects = !empty($values['subjects']) ? $values['subjects'] : array();
        }

        if ($this->isSubjectContext()) {
            $subjects[] = $this->_subjectId;

            $subject = $this->getOne($this->_subjectService->find($this->_subjectId));

            if ($subject) {
                $values['provider_id'] = $subject->provider_id;
            }
        }

        unset(
            $values['teacher_id'],
            $values['subjects'],
            $values['files']
        );

        $teacher = $this->_teacherService->insert($values);
        $teacherId = $teacher->teacher_id;

        foreach ($subjects as $subjectId) {
            $assignService->assign($teacherId, $subjectId);
        }

        $this->_addFiles($form, $teacherId);

    }

    public function update(HM_Form_TcTeacher $form)
    {
        /** @var HM_Tc_Provider_Teacher_Subject_SubjectService $assignService */
        $assignService = $this->getService('TcTeacherSubject');

        $values = $form->getValues();

        $subjects = !empty($values['subjects']) ? $values['subjects'] : array();

        unset(
            $values['subjects'],
            $values['files']
        );

        $teacher = $this->_teacherService->update($values);
        $teacherId = $teacher->teacher_id;

        $this->_addFiles($form, $teacherId);

        // в контексте курса назначение не редактируется
        if ($this->isSubjectContext()) {
            return;
        }

        if ($this->isProviderContext()) {

            $assignService->unAssign($teacherId, $this->_providerId);

            foreach ($subjects as $subjectId) {
                $assignService->assign($teacherId, $subjectId);
            }
        }

    }

    public function delete($id)
    {
        $this->_teacherService->delete($id);
    }

    /**
     * Да простит меня Бог за этот ****
     */
    public function deleteByAction()
    {
        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $params = $request->getParams();

        foreach ($params as $paramName => $param) {
            if (substr($paramName, 0, 11) === 'postMassIds') {
                $request->setParam('postMassIds_grid', $param);
                break;
            }
        }

        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);

            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
                $ids = $this->_teacherService->fetchAll(
                    $this->quoteInto(
                        array('teacher_id IN (?)', ' AND created_by=?'),
                        array($ids, $this->getService('User')->getCurrentUserId())
                    ))->getList('teacher_id');
            }

            if (count($ids)) {
                foreach($ids as $id) {
                    $this->delete($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    protected function _addFiles(HM_Form_TcTeacher $form, $teacherId)
    {
        /** @var HM_Files_FilesService $fileService */
        $fileService = $this->getService('Files');

        /** @var Zend_File_Transfer_Adapter_Http $files */
        $files = $form->files;

        // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
        $populatedFiles = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_TC_TEACHER, $teacherId);
        $deletedFiles   = $files->updatePopulated($populatedFiles);
        if(count($deletedFiles))
        {
            $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
        }

        if ($files->isUploaded() && $files->receive() && $files->isReceived()) {
            $files = $files->getFileName();

            if (!is_array($files)) {
                $files = array($files);
            }

            foreach ($files as $file) {
                $fileInfo = pathinfo($file);
                $fileService->addFile(
                    $file,
                    $fileInfo['basename'],
                    HM_Files_FilesModel::ITEM_TYPE_TC_TEACHER,
                    $teacherId
                );
            }
        }
    }

    protected function isSubjectContext()
    {
        return ($this->_subjectId > 0);
    }

    protected function isProviderContext()
    {
        return ($this->_providerId > 0);
    }

}