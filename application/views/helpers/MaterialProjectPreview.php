<?php
class HM_View_Helper_MaterialProjectPreview extends HM_View_Helper_Abstract
{

    public function materialProjectPreview($lesson)
    {
        if (!(is_a($lesson->material, 'HM_Course_CourseModel') || is_a($lesson->material, 'HM_Resource_ResourceModel'))) return '';

        $this->view->lesson = $lesson;
        $this->view->isStatsAllowed = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            is_a($lesson->material, 'HM_Course_CourseModel') &&
            in_array($lesson->material->format, HM_Course_CourseModel::getInteractiveFormats());

        $this->view->isEditAllowed = Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:extra-materials:edit');

        $this->view->launchUrl = $this->view->url(array(
            'action' => 'index',
            'controller' => 'execute',
            'module' => 'lesson',
            'lesson_id' => $lesson->meeting_id,
            'subject_id' => $lesson->project_id
        ), false, true);

        $this->view->statsUrl = $this->view->url(array(
            'action' => 'listlecture',
            'controller' => 'result',
            'module' => 'lesson',
            'lesson_id' => $lesson->meeting_id,
            'subject_id' => $lesson->project_id,
            'userdetail' => 'yes1',
            'switcher' => 'listlecture',
        ), false, true);

        $this->view->key = 'meeting_id';
        $this->view->lesson_id = $lesson->meeting_id;
        $this->view->subject_id = $lesson->project_id;

        return $this->view->render('material-preview.tpl');
    }
}