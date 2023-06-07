<?php
class HM_View_Helper_MeetingPreview extends HM_View_Helper_Abstract
{

    public function meetingPreview($meeting, $titles = null, $template = 'meeting-preview', $forUser = NULL)
    {
        if (empty($cols)) {
            $cols = array(
                '72px',
                'auto',
                '110px',
                '256px',
            );
        }

        $serviceContainer = Zend_Registry::get('serviceContainer');

        $this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/scoreList.js'));

/*        $this->view->allowEdit = $this->view->allowDelete = in_array(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER)
        );*/

        $this->view->allowEdit = $this->view->allowDelete = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_CURATOR/*, HM_Role_Abstract_RoleModel::ROLE_MODERATOR*/));

        $this->view->showScore = (
                (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ||
                        //Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_STUDENT ||
                (/*Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MODERATOR) && */ $forUser)) &&
                $meeting->vedomost
            );

        $types = HM_Event_EventModel::getMeetingTypes();

		if($meeting->timetype == 2) $datetime = _('Не ограничено');
		elseif($meeting->timetype == 1){
			
			// если возможно, показываем сразу абсолютные даты
			if ((Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ||
                (/*Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() ==HM_Role_Abstract_RoleModel::ROLE_MODERATOR && */ $forUser))) {
				
                $begin = $meeting->getBeginDateRelative($forUser);
                $end = $meeting->getEndDateRelative($forUser);

				if(!$begin) {
					$datetime = sprintf(_("до %s "), $end);
				} elseif(!$end) {
					$datetime = sprintf(_("с %s "), $begin); 
				} elseif ($begin != $end) {
					$datetime = sprintf(_("с %s по %s"), $begin, $end);				
				} else {
					$datetime = $begin;
				}
				
			} else {
				$begin = $meeting->getBeginDay();
				$end = $meeting->getEndDay();
				$strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
				if ($begin == $end)	$datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
				elseif(!$end)		$datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
				else 				$datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
			}
		}
        else{
			$begin = $meeting->getBeginDate();
			$end = $meeting->getEndDate();
			if ($begin == $end)	$datetime = sprintf(_("%s, с %s по %s"), $begin, $meeting->getBeginTime(), $meeting->getEndTime());
			elseif(!$end)		$datetime = sprintf(_("с %s "), $begin);
			else $datetime = sprintf(_("с %s по %s"), $begin, $end);
        }

        $details = 1;
        if($meeting->getType() == HM_Event_EventModel::TYPE_TEST){
            /** @var HM_Lesson_Test_TestModel $lesson */
            /** @var HM_Lesson_Test_TestService $lessonTestService */
            $meetingTestService = $serviceContainer->getService('MeetingTest');
            $quest = $meetingTestService->getQuest($meeting);
            if($quest->quest_id){
                $questSettings = $quest->getSettings();
            }
            $details = $questSettings->show_log;

        }

        $this->view->titleUrl = $this->view->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'meeting', 'subjecttype' => 'project', 'meeting_id' => $meeting->meeting_id, 'project_id' => $meeting->project_id), false, true);
        $this->view->targetUrl = '';

        if($meeting->getType() == HM_Event_EventModel::TYPE_COURSE){


            $courseId = $meeting->getModuleId();
            $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($courseId));
            if($course->new_window == 1){
                $itemId = Zend_Registry::get('serviceContainer')->getService('CourseItemCurrent')->getCurrent(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $meeting->CID, $courseId, $meeting->SHEID);
                if($itemId != false){
                    $this->view->titleUrl = $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId));
                    $this->view->targetUrl = '_blank';
                    //return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $field.'</a>';
                }
            }

        }

        $this->view->currentUserId = ($forUser)? $forUser : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $this->view->isStudentPageForTeacher = ($forUser && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER))? true : false;
		$this->view->type = $types[$meeting->typeID];
    	$this->view->titles = $titles;
        $this->view->details = $details;
    	$this->view->datetime = $datetime;
        $this->view->meeting = $meeting;
        $this->view->eventCollection = $eventCollection;
        $this->view->cols = $cols;

        if($meeting->teacher[0]->MID > 0){
		    $this->view->teacher = array('user_id' => $meeting->teacher[0]->MID, 'fio' => trim($meeting->teacher[0]->LastName.' '.$meeting->teacher[0]->FirstName.' '.$meeting->teacher[0]->Patronymic));
        }else{
            $this->view->teacher = null;
        }
        return $this->view->render($template . '.tpl');
    }
}