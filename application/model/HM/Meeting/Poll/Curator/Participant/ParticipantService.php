<?php
class HM_Meeting_Poll_Curator_Participant_ParticipantService extends HM_Meeting_Poll_Curator_CuratorService
{
        public function assignParticipants($meetingId, $participants, $unassign = true)
    {
        $meeting = $this->getService('Meeting')->getOne($this->getService('Meeting')->find($meetingId));
        if ($meeting) {

             parent::assignParticipants($meetingId, $participants, $unassign);

            /**
             * Посылаем уведомление о назначении кураторского опроса
             */
            $this->_sendAssignParticipantsMessage($meeting, $participants, HM_Messenger::TEMPLATE_POLL_PARTICIPANTS);

            return true;

        }
        return false;
    }

    public function getAvailableParticipants($projectId)
    {
        return $this->getService('Project')->getAssignedGraduated($projectId)->getList('MID', 'MID');
    }
}