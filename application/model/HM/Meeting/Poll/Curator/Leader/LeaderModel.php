<?php
class HM_Meeting_Poll_Curator_Leader_LeaderModel extends HM_Meeting_Poll_Curator_CuratorModel
{

    public function getType()
    {
        return HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER;
    }
    
    public function getServiceName()
    {
        return 'MeetingCuratorPollLeader';
    }

}