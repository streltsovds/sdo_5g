<?php
class HM_View_Helper_LessonPreview extends HM_View_Helper_Abstract
{
    /**
     * @param HM_Lesson_LessonModel $lesson
     * @param null $titles
     * @param string $template
     * @param null $forUser
     * @param null $eventCollection
     * @param null $cols
     * @return string
     */
    public function lessonPreview($lesson, $titles = null, $template = 'lesson-preview', $forUser = NULL, $eventCollection = null, $cols = null)
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

        $this->view->allowEdit = $this->view->allowDelete = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_TEACHER));

        $this->view->showScore = (
                (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ||
                        //Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_STUDENT ||
                    (((Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)) || Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) && $forUser)) &&
                $lesson->vedomost
            );
//#17849
        $types = HM_Event_EventModel::getTypes();
        $extTypes = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll();
        $extTypes = $extTypes->getList('event_id', 'title');
        foreach($extTypes as $i=>$e)
            $types[-$i] = $e;
//
        $aclService = Zend_Registry::get('serviceContainer')->getService('Acl');
        $userService = Zend_Registry::get('serviceContainer')->getService('User');

        // если смотрит сам юзер или препод/менеджер расписание пользователя
        // в этом случае показываем персональные даты из scheduleID
        if ($aclService->inheritsRole($userService->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) || $forUser) {

            $begin = $lesson->getBeginDatePersonal($forUser);
            $end = $lesson->getEndDatePersonal($forUser);

            $datetime = self::formatBeginEnd($begin, $end);

        } else {

            switch ($lesson->timetype) {

                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                    $datetime = _('Не ограничено');
                    break;

                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                    $begin = $lesson->getBeginDay();
                    $end = $lesson->getEndDay();

                    $strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
                    if ($begin == $end)	{
                        $datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
                    } elseif (!$end) {
                        $datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
                    } else {
                        $datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
                    }

                    break;

                case HM_Lesson_LessonModel::TIMETYPE_DATES:
                case HM_Lesson_LessonModel::TIMETYPE_TIMES:

                    $begin = $lesson->getBeginDate();
                    $end = $lesson->getEndDate();

                    $datetime = self::formatBeginEnd($begin, $end);

                    break;
            }
        }


        $details = 1;
        if($lesson->getType() == HM_Event_EventModel::TYPE_TEST){

            $details = true;

// очень тормозит
//            /** @var HM_Lesson_Test_TestModel $lesson */
//            /** @var HM_Lesson_Test_TestService $lessonTestService */
//            $lessonTestService = $serviceContainer->getService('LessonTest');
//            $quest = $lessonTestService->getQuest($lesson);
//            if($quest->quest_id){
//                $questSettings = $quest->getSettings();
//            }
//            $details = $questSettings->show_log;

        } elseif ($lesson->getType() == HM_Event_EventModel::TYPE_RESOURCE) {
            $details = 0;
        }

        $this->view->titleUrl = $this->view->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson->SHEID, 'subject_id' => $lesson->CID), false, true);
        $this->view->targetUrl = '';

        if($lesson->getType() == HM_Event_EventModel::TYPE_COURSE){


            $courseId = $lesson->getModuleId();
            $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($courseId));
            if($course->new_window == 1){
                $itemId = Zend_Registry::get('serviceContainer')->getService('CourseItemCurrent')->getCurrent(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $lesson->CID, $courseId, $lesson->SHEID);
                if($itemId != false){
                    $this->view->titleUrl = $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId, 'lesson_id' => $lesson->SHEID));
                    $this->view->targetUrl = '_blank';
                    //return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $field.'</a>';
                }
            }

        }

        $this->view->currentUserId = ($forUser)? $forUser : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $this->view->isStudentPageForTeacher = ($forUser && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_TEACHER, HM_Role_Abstract_RoleModel::ROLE_DEAN)))? true : false;
		$this->view->type = $types[$lesson->typeID];
    	$this->view->titles = $titles;
        $this->view->details = $details;
    	$this->view->datetime = $datetime;
        $this->view->lesson = $lesson;
        $this->view->eventCollection = $eventCollection;
        $this->view->cols = $cols;

        if($lesson->teacher[0]->MID > 0){
		    $this->view->teacher = array('user_id' => $lesson->teacher[0]->MID, 'fio' => trim($lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic));
        }else{
            $this->view->teacher = null;
        }
        return $this->view->render($template . '.tpl');
    }

    static public function formatBeginEnd($begin, $end)
    {
        $beginDate = HM_Model_Abstract::date($begin);
        $endDate = HM_Model_Abstract::date($end);

        if (!strtotime($begin) && !strtotime($end)) {
            $return = _('Не ограничено');
        } elseif (!strtotime($begin)) {
            $return = sprintf(_("по %s"), $endDate);
        } elseif (!strtotime($end)) {
            $return = sprintf(_("с %s"), $beginDate);
        } elseif ($beginDate == $endDate)	{
            $return = sprintf(_("%s, с %s по %s"), $beginDate, HM_Model_Abstract::timeWithoutSeconds($begin), HM_Model_Abstract::timeWithoutSeconds($end));
        } else {
            $return = sprintf(_("с %s по %s"), $beginDate, $endDate);
        }
        return $return;
    }
}