<?php
class HM_Meeting_Poll_Curator_CuratorModel extends HM_Meeting_Poll_PollModel
{
    
    static public function factory($data, $default = 'HM_Meeting_Poll_Curator_CuratorModel')
    {

        if (isset($data['typeID']))
        {
            switch($data['typeID']) {
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_LEADER:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Leader_LeaderModel');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_PARTICIPANT:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Participant_ParticipantModel');
                    break;
                case HM_Event_EventModel::TYPE_CURATOR_POLL_FOR_MODERATOR:
                    return parent::factory($data, 'HM_Meeting_Poll_Curator_Moderator_ModeratorModel');
                    break;
                default:
                    // Если занятие на основе сервиса взаимодействия
                    $activities = HM_Activity_ActivityModel::getActivityServices();
                    if (isset($activities[$data['typeID']])) {
                        $service = HM_Activity_ActivityModel::getActivityService($data['typeID']);
                        $class = Zend_Registry::get('serviceContainer')->getService($service)->getMeetingModelClass();
                        return parent::factory($data, $class);
                    }
                    break;
            }
        }
        if ($default != 'HM_Meeting_Poll_Curator_CuratorModel') {
            return parent::factory($data, $default);
        }
    }

    public function getServiceName()
    {
        return 'MeetingCuratorPoll';
    }

}