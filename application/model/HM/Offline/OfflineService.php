<?php
class HM_Offline_OfflineService extends HM_Service_Abstract
{
	static public function getOfflineTables($asArray = false)
	{
		$tables = array(
			'Courses',
			'events',
			'file',
			'files',
			'formula',
			'holidays',
			'interview',
			'interview_files',
			'library',
			'list',
			'list_files',
			'OPTIONS',
			'organizations',
			'People',
			'quizzes',
			'resources',
			'scales',
			'scale_values',
			'schedule',
			'scheduleID',
			'sections',
			'Students',
			'storage_filesystem',
			'subjects',
			'subjects_courses',
			'subjects_quizzes',
			'subjects_resources',
			'subjects_tasks',
			'subjects_quests',
			'tasks',
			'Teachers',
			'test',
			'tests_questions',
			'test_abstract',
		);
		
		return $asArray ? $tables : implode(' ', $tables);
	}
	
}