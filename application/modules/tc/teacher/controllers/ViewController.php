<?php

class Teacher_ViewController extends HM_Controller_Action
{
    /** @var HM_Tc_Provider_Teacher_TeacherService */
    protected $_teacherService = null;

    public function init()
    {
        parent::init();

        HM_Teacher_View_ExtendedView::init($this);

        $this->_teacherService = $this->getService('TcProviderTeacher');

    }

    public function cardAction()
    {
        $teacherId = $this->_getParam('teacher_id', 0);
        $teacher   = $this->getOne($this->_teacherService->find($teacherId));

        $this->view->assign(array(
            'canEdit' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)
                || ($teacher->created_by == $this->getService('User')->getCurrentUserId())),
            'teacher' => $teacher,
            'details'  => array(
                _('Файлы') => $this->_teacherService->getTeacherFiles($teacherId)
            )
        ));

    }

    protected function _card($teacher)
    {
        return $this->view->card(
            $teacher,
            array(
                'name'              => _('ФИО'),
                'getProviderName()' => _('Провайдер обучения'),
                'contacts'          => _('Контактные данные'),
                'description'       => _('Описание'),
                'getFiles()'        => _('Дополнительная информация')
            ),
            array(
                'title' => _('Карточка тьютора внешнего обучения'),
                'noico' => true
            )
        );
    }


}