<?php

/**
 *
 */
class HM_Subject_DataGrid_MassAction_Materials_CreateLessons extends HM_DataGrid_MassAction
{
	static public function create(HM_DataGrid $dataGrid, $name, array $options = array())
	{
		$serviceContainer = $dataGrid->getServiceContainer();
		$switchers = $dataGrid->getSwitcher();

		if ($serviceContainer->getService('Acl')->inheritsRole(
			$serviceContainer->getService('User')->getCurrentUserRole(),
			array(
				HM_Role_Abstract_RoleModel::ROLE_DEAN,
				HM_Role_Abstract_RoleModel::ROLE_TEACHER,
				HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
				HM_Role_Abstract_RoleModel::ROLE_CURATOR)
		)) {
			$url = array('module' => 'subject', 'controller' => 'lesson', 'action' => 'create-by-material');
			$url['subject_id'] = $options['subject_id'];

			$self = parent::create($dataGrid, $name, $options);
			$self->setUrl($url);
			$self->setConfirm(_('Данное действие может быть необратимым. Вы действительно хотите продолжить?'));

			return $self;
		}
	}
}