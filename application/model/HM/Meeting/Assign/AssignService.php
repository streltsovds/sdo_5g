<?php
class HM_Meeting_Assign_AssignService extends HM_Service_Abstract
{
    public function setUserScore($userId, $meetingsId, $score, $courseId = 0, $automatic = false){

        try {
            // не работает из unmanaged
            $currentUserId = $this->getService('User')->getCurrentUserId();

        } catch (Zend_Session_Exception $e) {
            $currentUserId = $GLOBALS['s']['mid'];
        }

        if ($meetingsId != "total") {

            if ($score === ''){
                $score = -1;
            }

            $collection = $this->fetchAllDependence('Meeting', array('MID = ?' => $userId, 'meeting_id = ?' => $meetingsId));
            if (count($collection)) {
                $meetingAssign = $collection->current();
                $meetingAssign->V_STATUS = (int) $score;
                $this->update($meetingAssign->getValues());

                $meeting = $meetingAssign->meetings->current();
            }

        } else {

            $one = $this->getOne($this->getService('ProjectMark')->fetchAll(array('mid = ?' => $userId, 'cid = ?' => $courseId)));

            $array = array(
                'mid' => $userId, 
                'cid' => $courseId, 
                'mark' => $score,
                'confirmed' => HM_Project_Mark_MarkModel::MARK_CONFIRMED, // если препод ставит оценку руками, то дополнительного подтверждения для слушателя не требуется
            );

            if ( $score != '' && $score != -1 ) {
                if( $one ) {
                    $this->getService('ProjectMark')->update($array);
                } else {
                    $this->getService('ProjectMark')->insert($array);
                }
            } else {
                $this->getService('ProjectMark')->deleteBy(array('mid = ?' => $userId, 'cid = ?' => $courseId));
            }
        }
    }

    public function setUserComments($userId, $meetingsId, $comment, $projectId = 0)
    {
        if ($meetingsId != "total"){
            $res = $this->updateWhere(array('comments' => $comment),array('MID = ?' => $userId, 'meeting_id = ?' => $meetingsId));
        } elseif ($projectId) {
            $where = $this->quoteInto(array('mid=?', ' AND cid=?'), array($userId, $projectId));
            $this->getService('ProjectMark')->updateWhere(array('comments' => $comment), $where);
        }
    }

    public function insert($data)
    {
        //$data['V_STATUS'] = ($mark = HM_Project_Mark_MarkModel::filterMark($data['V_STATUS'])) ? $mark : -1;
    	$data['created'] = $data['updated'] = $this->getDateTime();
    	return parent::insert($data);

    }

    public function update($data)
    {
        $data['V_STATUS'] = HM_Project_Mark_MarkModel::filterMark($data['V_STATUS']);
    	$data['updated'] = $this->getDateTime();
    	return parent::update($data);

    }

    public function onMeetingStart($meeting)
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)) {

            $userId = $this->getService('User')->getCurrentUserId();
            if (
                ($meeting->isfree == HM_Meeting_MeetingModel::MODE_PLAN) &&
                $meeting->getFormulaId() &&
                $meeting->vedomost
            ) {
                $score = false;
                // сюда добавлять логику обработки onStart для других типов занятий и других шкал
                switch ($meeting->getType()) {
                    case HM_Event_EventModel::TYPE_RESOURCE:
                        if ($meeting->getScale() == HM_Scale_ScaleModel::TYPE_BINARY) {
                            $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                        }
                    break;
                }

                if ($score !== false) {
                    $this->setUserScore($userId, $meeting->meeting_id, $score, 0, true);
                }

            } elseif ($meeting->isfree == HM_Meeting_MeetingModel::MODE_FREE) {

                // это для страницы "статистика изучения свободных материалов"
                $this->updateWhere(array(
                        'V_DONE' => HM_Meeting_Assign_AssignModel::PROGRESS_STATUS_INPROCESS,
                        'launched' => date('Y-m-d H:i:s'),
                   ),
                   array(
                       'meeting_id = ?'  => $meeting->meeting_id,
                       'MID = ?'    => $userId,
                       'V_DONE = ?' => HM_Meeting_Assign_AssignModel::PROGRESS_STATUS_NOSTART
                   )
                );
            }
        }
    }

    public function onMeetingFinish($meeting, $result)
    {
        try {
            // не работает из unmanaged
            $roleCondition = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT);
            $userId = $this->getService('User')->getCurrentUserId();

        } catch (Zend_Session_Exception $e) {
            $roleCondition = true;
            $userId = $GLOBALS['s']['mid'];
        }
        if ($roleCondition) {

            if (
                ($meeting->isfree == HM_Meeting_MeetingModel::MODE_PLAN) &&
                $meeting->vedomost
            ) {
                $score = false;
                // сюда добавлять логику обработки onFinish для других типов занятий и других шкал
                // @todo: рефакторить эти вложенные switch'и
                switch ($meeting->getType()) {
                    case HM_Event_EventModel::TYPE_COURSE:
                    case HM_Event_EventModel::TYPE_LECTURE:
                        if ($meeting->getFormulaId()) {
                            switch ($meeting->getScale()) {
                                case HM_Scale_ScaleModel::TYPE_BINARY:
                                    if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                                    }
                                break;
                                case HM_Scale_ScaleModel::TYPE_TERNARY:
                                    if (isset($result['status'])) {
                                        if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                            $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                                        } elseif ($result['status'] == HM_Scorm_Track_Data_DataModel::STATUS_FAILED) {
                                            $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                                        }
                                    }
                                break;
                                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                                    if (isset($result['score']) && isset($result['status'])) {
                                        if (in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                                            $score = $result['score'];
                                        } else {
                                            // сбрасываем при повторном прохождении
                                            $score = HM_Scale_Value_ValueModel::VALUE_NA;
                                        }
                                    }
                                break;
                            }
                        }
                    break;
                    case HM_Event_EventModel::TYPE_TEST:
                        switch ($meeting->getScale()) {
                            case HM_Scale_ScaleModel::TYPE_BINARY:
                                if (isset($result['score'])) {
                                    if (empty($meeting->threshold) || ($result['score'] >= $meeting->threshold)) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                                    }
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_TERNARY:
                                if (isset($result['score'])) {
                                    if (empty($meeting->threshold) || ($result['score'] >= $meeting->threshold)) {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                                    } else {
                                        $score = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                                    }
                                }
                                break;
                            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                                if (isset($result['score'])) {
                                    if ($formulaId = $meeting->getFormulaId()) {
                                        $formula = $this->getService('Formula')->getById($formulaId);
                                        if ($formula) {
                                            $score = $formula->getResultValue($result['score']);
                                        }
                                    } else {
                                        $score = $result['score'];
                                    }
                                }
                                break;
                        }
                        break;
                }

                if ($score !== false) {
                    $this->setUserScore($userId, $meeting->meeting_id, $score, 0, true);
                }

            } elseif ($meeting->isfree == HM_Meeting_MeetingModel::MODE_FREE) {

                // это для страницы "статистика изучения свободных материалов"
                if (isset($result['status']) && in_array($result['status'], HM_Scorm_Track_Data_DataModel::getSuccessfullStatuses())) {
                    $this->updateWhere(array(
                            'V_DONE' => HM_Meeting_Assign_AssignModel::PROGRESS_STATUS_DONE,
                       ),
                       array(
                           'meeting_id = ?'  => $meeting->meeting_id,
                           'MID = ?'    => $userId,
                           'V_DONE = ?' => HM_Meeting_Assign_AssignModel::PROGRESS_STATUS_INPROCESS
                       )
                    );
                }
            }
        }
    }
}