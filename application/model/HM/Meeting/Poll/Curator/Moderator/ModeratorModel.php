<?php
class HM_Meeting_Poll_Curator_Moderator_ModeratorModel extends HM_Meeting_Poll_Curator_CuratorModel
{

    public function getType()
    {
        return HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR;
    }

    public function getServiceName()
    {
        return 'MeetingCuratorPollModerator';
    }
  
}