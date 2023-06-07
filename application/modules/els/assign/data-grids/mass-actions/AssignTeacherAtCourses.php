<?php

/**
 *
 */
class HM_Assign_DataGrid_MassAction_AssignTeacherAtCourses extends HM_DataGrid_MassAction
{
    static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
    {
        $options['courseId'] = isset($options['courseId']) ? $options['courseId'] : 0;
        $multiple = isset($options['multiple']) ? $options['multiple'] : false;
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

            $assignMenuItem   = '';
            // заголовок действия назначения на курс в зависимости от контроллера
            switch ( Zend_Controller_Front::getInstance()->getRequest()->getControllerName() ) {
                case 'teacher':
                    if ($serviceContainer->getService('Acl')->inheritsRole(
                        $serviceContainer->getService('User')->getCurrentUserRole(),
                        array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))) {
                        break;
                    }
                    $assignMenuItem = ($subjectId > 0) ? _('Назначить тьюторов на курс') : _('Назначить тьюторов на курсы');
                    break;
                case 'student':
                    if (!$subject || ($subject->state != HM_Subject_SubjectModel::STATE_CLOSED)) {
                        $assignMenuItem = ($subjectId > 0) ? _('Назначить слушателей на курс') : _('Назначить слушателей на курсы');
                    }
                    break;
                case 'participant':
                    if (!$subject || ($subject->state != HM_Project_ProjectModel::STATE_CLOSED)) {
                        $assignMenuItem = ($subjectId > 0) ? _('Назначить участников на конкурс') : _('Назначить участников на конкурсы');
                    }
                    break;
                default:
                    if($serviceContainer->getService('Acl')->inheritsRole(
                        $serviceContainer->getService('User')->getCurrentUserRole(),
                        HM_Role_Abstract_RoleModel::ROLE_TEACHER)) {
                        break;
                    }
                    $assignMenuItem = ($subjectId > 0) ? _('Назначить на курс') : _('Назначить на курсы');
                    break;
            }

            $url = array(
                'module' => 'assign',
                'controller' => 'teacher',
                'action' => 'assign',
            );

            if ($subjectId > 0) {
                $url['courseId']   = $subjectId;
                $url['subject_id'] = $subjectId;
            }

            $self = parent::create($dataGrid, $name, $options);

            $self->setName($assignMenuItem);
            $self->setUrl($url);
            $self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

            $coursesPrompt = _('Выберите курс');
            $userId = $serviceContainer->getService('User')->getCurrentUserId();
            //для назначения на курсы должны отображать список активных курсов
            $collection = $serviceContainer->getService('Dean')->getActiveSubjectsResponsibilities($userId);

            if ($subjectId <= 0) {
                $courses = array();
                if (count($collection)) {
                    $courses = $collection->getList('subid', 'name');
                }

                $courses[0] = $coursesPrompt;
                $self->setSub(array(
                    'function' => self::SUB_MASS_ACTION_SELECT,
                    'params'   => array(
                        'url'     => $dataGrid->getView()->url($self->getUrl()),
                        'name'    => 'courseId',
                        'options' => $courses,
                        'multiple' => $multiple
                    )
                ));
            }

            return $self;
        }

        return false;
    }
}