<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_UnAssignTeacherFromCourses extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $serviceContainer = $dataGrid->getServiceContainer();
        if ($serviceContainer->getService('Acl')->inheritsRole(
            $serviceContainer->getService('User')->getCurrentUserRole(),
            [
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR
            ]
        )) {

            $subject = isset($options['courseId']) ?
                $serviceContainer->getService('Subject')->find($options['courseId'])->current() : null;

            $subjectId = $subject ? $subject->subid : 0;
            $unassignMenuItem = '';

            // заголовок действия назначения на курс в зависимости от контроллера
            switch ( Zend_Controller_Front::getInstance()->getRequest()->getControllerName() ){
                case 'teacher':
                    if($serviceContainer->getService('Acl')->inheritsRole(
                        $serviceContainer->getService('User')->getCurrentUserRole(),
                        array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
                        break;
                    }
                    $unassignMenuItem = ($subjectId > 0) ? _('Отменить назначение тьюторов') : _('Отменить назначения тьюторов');
                    break;
                case 'student':
                    if (!$subject || ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED)) {
                        $unassignMenuItem = ($subjectId > 0) ? _('Отменить назначение слушателей') : _('Отменить назначения слушателей');
                    }
                    break;
                case 'participant':
                    if (!$subject || ($subject->state != HM_Project_ProjectModel::STATE_CLOSED)) {
                        $unassignMenuItem = ($subjectId > 0) ? _('Отменить назначение участников') : _('Отменить назначения участников');
                    }
                    break;
                default:
                    if($serviceContainer->getService('Acl')->inheritsRole(
                        $serviceContainer->getService('User')->getCurrentUserRole(),
                        HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
                        break;
                    }
                    $unassignMenuItem = ($subjectId > 0) ? _('Отменить назначение на курс') : _('Отменить назначение на курсы');
                    break;
            }

            $url = array(
                'module' => 'assign',
                'controller' => 'teacher',
                'action' => 'unassign',
            );

            if ($subjectId > 0) {
                $url['courseId'] = $subject->subid;
                $url['subject_id'] = $subject->subid;
            }

            $self = parent::create($dataGrid, $name, $options);

            $self->setName($unassignMenuItem);
            $self->setUrl($url);
            $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $coursesPrompt = _('Выберите курс');
            $userId = $serviceContainer->getService('User')->getCurrentUserId();
            //для назначения на курсы должны отображать список активных курсов, для удаления - список всех курсов
            $collection = $serviceContainer->getService('Dean')->getSubjectsResponsibilities($userId);

            if ($subjectId <= 0) {
                $courses = array();
                if (count($collection)) {
                    $courses = $collection->getList('subid', 'name', $coursesPrompt);
                }

                $self->setSub(array(
                    'function' => self::SUB_MASS_ACTION_SELECT,
                    'params'   => array(
                        'url'     => $dataGrid->getView()->url($self->getUrl()),
                        'name'    => 'unCourseId[]',
                        'options' => $courses
                    )
                ));
            }

            return $self;
        }
        return false;
    }
}