<?php
class HM_Tc_Session_Department_Application_ApplicationTable extends HM_Db_Table
{
	protected $_name = "tc_department_applications";
	protected $_primary = "department_application_id";

	protected $_referenceMap = array(
		'TcApplication' => array(
			'columns'       => 'department_application_id',
			'refTableClass' => 'HM_Tc_Application_ApplicationTable',
			'refColumns'    => 'department_application_id',
			'propertyName'  => 'tcapplications' // имя свойства текущей модели куда будут записываться модели зависимости
		),
		'Profile' => array(
			'columns'       => 'profile_id',
			'refTableClass' => 'HM_At_Profile_ProfileTable',
			'refColumns'    => 'profile_id',
			'propertyName'  => 'profiles' // имя свойства текущей модели куда будут записываться модели зависимости
		),
		'Subject' => array(
			'columns'       => 'subject_id',
			'refTableClass' => 'HM_Subject_SubjectTable',
			'refColumns'    => 'subid',
			'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
		),
		'Department' => array(
			'columns'       => 'department_id',
			'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
			'refColumns'    => 'soid',
			'propertyName'  => 'departments' // имя свойства текущей модели куда будут записываться модели зависимости
		),
	);

}