<?php
class HM_Meeting_Poll_Curator_Participant_ParticipantModel extends HM_Meeting_Poll_Curator_CuratorModel
{


    public function getType()
    {
        return HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT;
    }
    
    public function getServiceName()
    {
        return 'MeetingCuratorPollParticipant';
    }

}