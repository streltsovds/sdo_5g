<?php

class Teacher_ListController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        HM_Teacher_View_ExtendedView::init($this);

    }

    public function indexAction()
    {
        /** @var HM_Tc_Provider_Teacher_TeacherService $teacherService */
        $teacherService = $this->getService('TcProviderTeacher');

        $view       = $this->view;
        $subjectId  = $this->_getParam('subject_id', 0);
        $providerId = $this->_getParam('provider_id', 0);

        $grid = HM_Teacher_Grid_TeacherGrid::create(array(
            'controller' => $this,
            'courseId'   => $subjectId,
            'providerId' => $providerId
        ));

        $listSource = $teacherService->getListSource(array(
            'subjectId'   => $subjectId,
            'providerId'  => $providerId,
            'type'        => HM_Tc_Provider_ProviderModel::TYPE_PROVIDER
        ));

        $view->assign(array(
            'grid' => $grid->init($listSource)
        ));

    }

}