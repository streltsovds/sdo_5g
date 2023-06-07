<?php
class HM_Scorm_Track_TrackService extends HM_Service_Abstract
{
    public function getUserTrackData($userId, $courseId, $itemId, $moduleId, $lessonId)
    {
        $trackData = new HM_Scorm_Track_Data_DataModel(array());

        $tracks =
            $this->fetchAll(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?', ' AND ModID= ?', ' AND McID = ?', ' AND lesson_id = ?'),
                    array($userId, $courseId, $itemId, $moduleId, $lessonId)
                ),
                'trackID'
        );

        $trackData = new HM_Scorm_Track_Data_DataModel(array());

        $trackData->student_id = $userId;
        $trackData->student_name = sprintf(_('Пользователь #%d'), $userId);

        $user = $this->getOne($this->getService('User')->find($userId));
        if ($user) {
            $trackData->student_name = $user->getName();
        }

        $module = $this->getOne($this->getService('Library')->find($moduleId));
        if ($module) {
            $scormParams = $module->getScormParams();
            if (isset($scormParams['datafromlms'])) {
                $trackData->datafromlms = $scormParams['datafromlms'];
            }
            if (isset($scormParams['masteryscore'])) {
                $trackData->masteryscore = $scormParams['masteryscore'];
            }
            if (isset($scormParams['maxtimeallowed'])) {
                $trackData->maxtimeallowed = $scormParams['maxtimeallowed'];
            }
            if (isset($scormParams['timelimitaction'])) {
                $trackData->timelimitaction = $scormParams['timelimitaction'];
            }
        }

        if (count($tracks)) {
            foreach($tracks as $track) {
                $trackData->merge($track->getData());
            }

            if (in_array($trackData->getValue('cmi.exit'), array('logout', 'suspend'))) {
                $trackData->setValue('cmi.entry', 'resume');
            } else {
                $trackData->setValue('cmi.entry', '');
            }

            if (in_array($trackData->getValue('cmi.core.exit'), array('suspend'))) {
                $trackData->setValue('cmi.core.entry', 'resume');
            } else {
                $trackData->setValue('cmi.core.entry', '');
            }

        } else {
            $trackData->setValue('cmi.entry', 'ab-initio');
            $trackData->setValue('cmi.core.entry', 'ab-initio');
        }

        if (
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
            //!in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_STUDENT))
        ) {
            $trackData->mode = HM_Scorm_Track_Data_DataModel::MODE_BROWSE;
        }

        return $trackData;
    }

    public function getLastUserTrack($userId, $courseId, $itemId, $moduleId, $lessonId)
    {
	    $track = $this->fetchRow(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?', ' AND ModID = ?', ' AND McID = ?', ' AND lesson_id = ?'),
                    array($userId, $courseId, $itemId, $moduleId, $lessonId)
                ),
                'trackID DESC'
        );

        return $track;
    }

    public function getLastSuccessfulUserTrack($userId, $courseId, $itemId, $moduleId, $lessonId)
    {
        $track = $this->fetchRow(
            $this->quoteInto(
                array('mid = ?', ' AND cid = ?', ' AND ModID = ?', ' AND McID = ?', ' AND lesson_id = ?', ' AND STATUS IN (?)'),
                array($userId, $courseId, $itemId, $moduleId, $lessonId, HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())
            ),
            'trackID DESC'
        );


        return $track;
    }

    public function getLastUserTracks($userId, $courseId, $lessonId, $statuses = array())
    {
        $select = $this->getSelect();

        $select->from(array('st' => 'scorm_tracklog'), array('module' => new Zend_Db_Expr('DISTINCT ModID'), 'trackID'))
               ->where('mid =?',  $userId)
               ->where('cid = ?' , $courseId)
               ->where('lesson_id = ?', $lessonId)
               ->order(array('trackID'));
               
               
        if($statuses != array()){
            $select->where('status IN (?)', $statuses);            
        }
               
        return $select->query()->fetchAll();
        
        
    }
    

    public function isUserTrackDataExists($userId, $courseId, $itemId, $moduleId, $lessonId, $time)
    {
        $trackData = $this->getOne(
            $this->fetchAll(
                $this->quoteInto(
                    array('mid = ?', ' AND cid = ?', ' AND ModID = ?', ' AND McID = ?', ' AND lesson_id = ?',' AND start = ?'),
                    array($userId, $courseId, $itemId, $moduleId, $lessonId, $this->getDateTime($time))
                )
            )
        );

        return $trackData;
    }

    public function storeUserTrackData($post, $userId, $courseId, $itemId, $moduleId, $lessonId, $time)
    {
        if (!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER))) return true;

        $data = array();

        $cmi_completion_status = HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED;
        $cmi_score_raw = 0;
        $cmi_score_min = 0;
        $cmi_score_max = 0;

        $track = $this->isUserTrackDataExists($userId, $courseId, $itemId, $moduleId, $lessonId, $time);

        if (!$track) {

        	// Сначала попробуем найти успешный трек, вдруг занятие уже пройдено.
	        // Это выставил оценку без перепрохождения, если раньше из-за бага она не выставилась
            $lastTrack = $this->getLastSuccessfulUserTrack($userId, $courseId, $itemId, $moduleId, $lessonId);

            // Не нашли успешный - берём просто последний
            if(!$lastTrack)
            $lastTrack = $this->getLastUserTrack($userId, $courseId, $itemId, $moduleId, $lessonId);

            if ($lastTrack) {
            	// Если не закончено - будем продолжать, либо возьмём успешный статус и пересчитаем оценку в Course_ApiController::storeDataAction
                 if (((in_array($lastTrack->getDataValue('cmi.core.exit'), array(HM_Scorm_Track_Data_DataModel::EXIT_SUSPEND)))
			            || in_array($lastTrack->getDataValue('cmi.exit'), array(HM_Scorm_Track_Data_DataModel::EXIT_SUSPEND, HM_Scorm_Track_Data_DataModel::EXIT_LOGOUT, HM_Scorm_Track_Data_DataModel::EXIT_NORMAL)))
                        || ((null == $lastTrack->getDataValue('cmi.exit')) && (null == $lastTrack->getDataValue('cmi.core.exit')))) {
                    $cmi_score_max = $lastTrack->scoremax;
                    $cmi_score_min = $lastTrack->scoremin;
                    $cmi_score_raw = $lastTrack->score;
                    $cmi_completion_status = $lastTrack->status;
                }
            }
        }

        if (is_array($post) && count($post)) {

            foreach($post as $element => $value) {
                if (substr($element,0,3) == 'cmi') {
                    $element = str_replace('__','.',$element);
                    $element = preg_replace('/_(\d+)/',".\$1",$element);

                    switch($element) {
                        case 'cmi.core.lesson_status': // SCORM 1.2 
                            if ( in_array($value, HM_Scorm_Track_Data_DataModel::getLessonStatusVocabulary()) ) {
                                $cmi_completion_status = $value;

                                if ($cmi_completion_status == HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED_RAW) {
                                    $cmi_completion_status = HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED;
                                }

                                if ($track) {
                                    $track->status = $cmi_completion_status;
                                }
                            }
                        break;
                        case 'cmi.completion_status': // SCORM 2004
                        case 'cmi.success_status':
                            $completionStatus        = $post['cmi__completion_status'];
                            $successStatus           = $post['cmi__success_status'];
                            $isValidCompletionStatus = in_array($completionStatus, HM_Scorm_Track_Data_DataModel::getCompletionStatusVocabulary());
                            $isValidSuccessStatus    = in_array($successStatus,    HM_Scorm_Track_Data_DataModel::getSuccessStatusVocabulary());

                            if ( $isValidCompletionStatus || $isValidSuccessStatus ) {
                                if ( $isValidCompletionStatus && ($successStatus == HM_Scorm_Track_Data_DataModel::STATUS_UNKNOWN || !$isValidSuccessStatus) ) {
                                    $cmi_completion_status = $completionStatus;
                                } else if ( $isValidSuccessStatus ) {
                                    $cmi_completion_status = $successStatus;
                                }

                                if ($cmi_completion_status == HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED_RAW) {
                                    $cmi_completion_status = HM_Scorm_Track_Data_DataModel::STATUS_NOT_ATTEMPTED;
                                }

                                if ($track) {
                                    $track->status = $cmi_completion_status;
                                }
                            }
                        break;
                        case 'cmi.core.score.raw':
                        case 'cmi.score.raw':
                            if ($track) {
                                $track->score = $value;
                            }
                            $cmi_score_raw = $value;
                        break;
                        case 'cmi.core.score.min':
                        case 'cmi.score.min':
                            if ($track) {
                                $track->scoremin = $value;
                            }
                            $cmi_score_min = $value;
                        break;
                        case 'cmi.core.score.max':
                        case 'cmi.score.max':
                            if ($track) {
                                $track->scoremax = $value;
                            }
                            $cmi_score_max = $value;
                        break;
                        /*case 'cmi.core.score.scaled':
                        case 'cmi.score.scaled':
                            $cmi_score_raw = $value;
                            $cmi_score_max = 1;
                            $cmi_score_min = -1;
                        break;*/
                    }

                    $data[$element] = $value;
                }
            }

        }

        if ($track) {
            $track->stop = $this->getDateTime();

            $trackData = $track->getData();
            $trackData->mergeValues($data);

            $track->setData($trackData);
// этот функционал ушёл в onLessonFinish            
//             $lesson = $this->getOne($this->getService('Lesson')->fetchAll($this->quoteInto('SHEID = ?',$track->lesson_id)));
//             $form = $lesson->getFormulaId();
//             if($form)
//                 $this->getService('LessonAssign')->setUserScore($track->mid, $track->lesson_id, $track->score, $track->cid);
            return $this->update($track->getValues());
        }

        return $this->insert(
            array(
                'mid' => $userId,
                'cid' => $courseId,
                'ModID' => $itemId,
                'McID' => $moduleId,
                'lesson_id' => $lessonId,
                'trackdata' => serialize($data),
                'start' => $this->getDateTime($time),
                'stop' => $this->getDateTime(),
                'score' => (float) $cmi_score_raw,
                'scoremax' => (float) $cmi_score_max,
                'scoremin' => (float) $cmi_score_min,
                'status' => $cmi_completion_status
             )
        );
    }
    
    public function getAggregatedResults($courseId, $lessonId, $userId, $items = array()) 
    {
        if (!count($items)) {
            /** @var HM_Course_Item_ItemService $courseItemService */
            $courseItemService = $this->getService('CourseItem');
            $items = $courseItemService->getTreeItems($courseId);
        }
        
        $where = array(
            'mid = ?' => $userId, 
            'cid = ?' => $courseId, 
            'lesson_id = ?' => $lessonId,
        );
        
        if (count($items)) {
            $where['ModId IN (?)'] = $items->getList('oid');
        }
        $results = $this->getService('ScormTrack')->fetchAll($where);
        
        $itemResults = array();

        foreach($items as $item){
            if($item->module > 0){
                $maxResult = new stdClass();
                foreach($results as $result){

	                if(HM_Scorm_Track_Data_DataModel::isSuccessful($maxResult->status))
		                continue;

                	// Это результат нашего занятия и оно новое
                    if($item->oid == $result->ModID && strtotime($maxResult->stop) < strtotime($result->stop)){
                            $maxResult = $result;
                        }
                    }
                $item->result = $maxResult;
            }
            $itemResults[] = $item;            
        }

        $fullProgress = count($itemResults) ? HM_Course_CourseModel::PROGRESS_COMPLETED : HM_Course_CourseModel::PROGRESS_INCOMPLETE;
        foreach($itemResults as $item){
	        $result = $item->result;
	        if (!$result) continue;
            if (!HM_Scorm_Track_Data_DataModel::isSuccessful($result->status)){
                $fullProgress = HM_Course_CourseModel::PROGRESS_INCOMPLETE;
            }
        }
        
        return array($itemResults, $fullProgress);
    }    



}