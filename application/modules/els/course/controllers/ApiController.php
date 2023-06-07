<?php
class Course_ApiController extends HM_Controller_Action
{
    public function init()
    {
        $this->setEmpty();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function scormAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $courseId = (int) $this->_getParam('course_id', 0);
        $itemId   = (int) $this->_getParam('item_id', 0);
        $moduleId = (int) $this->_getParam('module_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);

        $userTrackData = $this->getService('ScormTrack')->getUserTrackData(
            $this->getService('User')->getCurrentUserId(),
            $courseId,
            $itemId,
            $moduleId,
            $lessonId
        );

        $module = $this->getOne(
            $this->getService('Library')->find($moduleId)
        );

        $content = 'SCORM_1.2';
        if($this->view->emulateScorm) {
            $content = 'SCORM_1.3';
        } elseif ($module) {
            $content = $module->content;
        }

        $this->view->userTrackData = $userTrackData;
        $this->view->requestUrl = $this->view->url(
            array(
                'module' => 'course',
                'controller' => 'api',
                'action' => 'store-data',
                'course_id' => $courseId,
                'item_id' => $itemId,
                'module_id' => $moduleId,
                'lesson_id' => $lessonId,
                'time' => time()
            )
        );

        $this->view->debug = $this->getService('Option')->getOption('scorm_tracklog'); //Zend_Registry::get('config')->scorm->debug;

        switch($content) {
            case 'SCORM_1.3':
                $userTrackData->entry = $userTrackData->{'cmi.entry'};
                $this->render('scorm13');
                break;
            default:
                $userTrackData->entry = $userTrackData->{'cmi.core.entry'};
                $this->render('scorm');
                break;
        }

        //$this->getHelper('viewRenderer')->setNoRender();
    }

    public function hacpAction()
    {

//         if (Zend_Registry::get('config')->scorm->debug) {
        if ($this->getService('Option')->getOption('scorm_tracklog')) {
            $this->getService('FireBug')->log('course/api/hacp dispatched...', Zend_Log::INFO);
            Zend_Registry::get('log_system')->debug('course/api/hacp dispatched...');
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

        $_GET['subject_id'] = $this->_getParam('subject_id', 0);
        $_GET['user_id'] = $this->getService('User')->getCurrentUserId();
        $_GET['lesson_id'] = $this->_getParam('lesson_id', 0);
        $_GET['time'] = $this->_getParam('time', 0);

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $s = &$_SESSION['s'];
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        include(APPLICATION_PATH.'/../public/unmanaged/hacp_datamodel.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));
        chdir($currentDir);

        // @todo: реализовать сохранение текущего процентажа  при любом статусе прохождения но и без лишней нагрузки на БД
        if ( isset($GLOBALS['hasp_track_status']) && isset($GLOBALS['hasp_track_score'])  ) {

// эта логика ушла в onLessonFinish        
//             $lesson = $this->getUserLesson($_GET['lesson_id'],$_GET['user_id']);
//             if ( in_array($GLOBALS['hasp_track_status'],
//                                      array(HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED,
//                                            HM_Scorm_Track_Data_DataModel::STATUS_PASSED)) ) {
//                 $lessonDoneStatus = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE;
//             } else {
//                 $lessonDoneStatus = HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS;
//             }
//             if ( $lesson ) {
//                 $subject = $this->getOne($lesson->subject);

//                 if ( $lesson->isfree == HM_Lesson_LessonModel::MODE_FREE ) {
//                     $lesson->V_STATUS = (float) $GLOBALS['hasp_track_score'];
//                 } else {
//                     //@todo: для регламентированного курса считать по формулам
//                 }
//                 $lesson->V_DONE   = $lessonDoneStatus;
//                 $this->getService('LessonAssign')
//                     ->update($lesson->getData());
//             }

            if (count($collection = $this->getService('Lesson')->find($_GET['lesson_id']))) {
                $this->getService('LessonAssign')->onLessonFinish($collection->current(), array(
                    'status' => $GLOBALS['hasp_track_status'],       
                    'score' => $GLOBALS['hasp_track_score'],       
                ));
            }
            
            unset($GLOBALS['hasp_track_status']);
            unset($GLOBALS['hasp_track_score']);
        }

        $this->view->content = $content;
    }

    public function storeDataAction()
    {

        if ($this->getService('Option')->getOption('scorm_tracklog')) {
            $this->getService('FireBug')->log('course/api/store-data dispatched...', Zend_Log::INFO);
            Zend_Registry::get('log_system')->debug('course/api/store-data dispatched...');
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $this->getResponse()->setHeader('Content-type', 'application/x-www-form-urlencoded');

        $courseId = (int)$this->_getParam('course_id', 0);
        $itemId = (int)$this->_getParam('item_id', 0);
        $moduleId = (int)$this->_getParam('module_id', 0);
        $lessonId = (int)$this->_getParam('lesson_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();
        $request = $this->getRequest();

        $track = $this->getService('ScormTrack')->storeUserTrackData($request->getPost(), $userId, $courseId, $itemId, $moduleId, $lessonId, $this->_getParam('time', time()));

        if ($track) {
            echo "true\n0";
        } else {
            echo "false\n101";
        }

        if (count($collection = $this->getService('Lesson')->find($lessonId))) {

            $lesson = $collection->current();

            if (!in_array($track->status, HM_Scorm_Track_Data_DataModel::getNonMarkableStatuses())) {

                $items = array();
                if ($lesson->getType() == HM_Event_EventModel::TYPE_LECTURE) {

                    if ($params = $lesson->getParams()) {
                        /** @var HM_Course_Item_ItemService $courseItemService */
                        $courseItemService = $this->getService('CourseItem');
                        $items = $courseItemService->getTreeItems($courseId, $lesson->getModuleId());
                    }
                }
                list(, $fullProgress) = $this->getService('ScormTrack')->getAggregatedResults($courseId, $lessonId, $userId, $items);

                if (
                    (HM_Course_CourseModel::PROGRESS_COMPLETED === $fullProgress and
                        in_array($track->status, HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) or
                    !in_array($track->status, HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())
                ) {

                        $this->getService('LessonAssign')->onLessonFinish($lesson, array(
                            'status' =>  $track->status,
                            'score' => $track->score, // @todo: здесь возможно баг!!! не факт, что это score за всё занятие - возможно только за часть занятия
                        ));
                    }
                }
            }
        exit();
    }

    public function storeSkillsoftDataAction()
    {

        if (APPLICATION_ENV == 'development') {
            $this->getService('FireBug')->log('course/api/store-skillsoft-data dispatched...', Zend_Log::INFO);
            Zend_Registry::get('log_system')->debug('course/api/store-skillsoft-data dispatched...');
        }

        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $this->getResponse()->setHeader('Content-type', 'application/x-www-form-urlencoded');

        $lessonId = (int) $this->_getParam('lesson_id', 0);

        if ($html = $this->_getParam('html')) {
            $report = $this->getService('ScormReport')->storeReport($html, $this->getService('User')->getCurrentUserId(), $lessonId);
        }

        if ($report) {
            echo "true\n0";
        } else {
            echo "false\n101";
        }
        exit();
    }


    private function getUserLesson($sheid,$mid = null)
    {
        if (!$sheid) {
            return false;
        }
        if ( !$mid ) {
            $mid = $this->getService('User')->getCurrentUserId();
        }

        return $this->getService('LessonAssign')
                    ->getOne($this->getService('LessonAssign')
                                  ->fetchAllManyToMany('Subject',
                                                       'Lesson',
                                                       array('SHEID = ?'=> $sheid,
                                                             'MID = ?'  => $mid)));
    }
}