<?php
class HM_Meeting_Poll_Curator_Moderator_ModeratorService extends HM_Meeting_Poll_Curator_CuratorService
{
    public function assignParticipants($meetingId, $participants, $unassign = true)
    {
        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
        if ($meeting) {
            $participants = $this->getService('Project')->getAssignedModerators($meeting->CID)->getList('MID', 'MID');

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
            $this->_sendAssignParticipantsMessage($meeting, $participants, HM_Messenger::TEMPLATE_POLL_MODERATORS);

            return true;

        }
        return false;
    }

    public function getAvailableParticipants($projectId)
    {
        return $this->getService('Project')->getAssignedGraduated($projectId)->getList('MID', 'MID');
    }
}