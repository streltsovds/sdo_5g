<?php
class HM_Meeting_Poll_Curator_Leader_LeaderService extends HM_Meeting_Poll_Curator_CuratorService
{
    public function assignParticipants($meetingId, $participants, $unassign = true)
    {
        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
        if ($meeting && count($participants)) {
            $collection = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', $participants));
            $participants = $leaders = array();
            if (count($collection)) {
                foreach($collection as $user) {
                    if ($user->head_mid > 0) {
                        $participants[$user->head_mid] = $user->head_mid;
                        $leaders[$user->head_mid][$user->MID] = $user->MID;
                    }
                }

                if (count($leaders)) {
                    foreach($leaders as $leaderId => $slaves) {
                        $this->getService('MeetingCuratorPollAssign')->assignParticipants($meetingId, $slaves, $leaderId, $unassign);
                    }
                }
            }

            if (count($participants)) {
                $this->getService('MeetingAssign')->deleteBy(
                    $this->quoteInto(
                        array('meeting_id = ?', ' AND MID IN (?)'),
                        array($meetingId, $participants)
                    )
                );
            }

            parent::assignParticipants($meetingId, $participants, $unassign);

            /**
             * Посылаем уведомление о назначении кураторского опроса
             */
            $this->_sendAssignParticipantsMessage($meeting, $participants, HM_Messenger::TEMPLATE_POLL_LEADERS, $leaders);

            return true;

        }
        return false;
    }
    
    public function isParticipantAssigned($participantId, $meetingId)
    {
        
        $res = $this->getService('MeetingCuratorPollAssign')->fetchAll(array('participant_mid = ?' => $participantId, 'meeting_id = ?' => $meetingId));
        
        if(count($res) > 0)
            return true;
        else 
            return false;
    }

    public function getAvailableParticipants($projectId)
    {
        return $this->getService('Project')->getAssignedGraduated($projectId)->getList('MID', 'MID');
    }
    
}