<?php
/*
 * Тест
 */
class HM_Meeting_Test_TestService extends HM_Meeting_MeetingService
{

    /**
     * Устанавливает настройки теста в занятии,
     * ищет настройки в порядке возрастания области видимости:
     * занятие -> курс -> база_знаний
     *
     * @param HM_Quest_QuestModel $quest
     * @param HM_Meeting_MeetingModel $meeting
     */
    private function _setQuestMeetingSettings($quest, $meeting) {
        if (!$quest || !$meeting) {
            return;
        }

        $questSettings = $quest->getScopeSettings(
            HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON,
            $meeting->SHEID
        );

        if (!$questSettings) {
            $questSettings = $quest->getScopeSettings(
                HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT,
                $meeting->CID
            );
        }

        if (!$questSettings) {
            $questSettings = $quest->getScopeSettings(
                HM_Quest_QuestModel::SETTINGS_SCOPE_GLOBAL
            );
        }
    }

    /**
     * Возвращает тест с настройками для занятия
     * @param HM_Meeting_MeetingModel|int $meeting
     * @return HM_Quest_QuestModel|null
     */
    public function getQuest($meeting) {
        if (!($meeting instanceof HM_Meeting_MeetingModel)) {
            $meeting = $this->getMeeting($meeting);
            if (!$meeting) {
                return null;
            }
        }

        $questId = $meeting->getModuleId();

        if (empty($questId)) {
            return null;
        }

        /** @var HM_Quest_QuestService $questService */
        $questService = $this->getService('Quest');
        $quest = $questService->getQuestWithSettings($questId);
        $this->_setQuestMeetingSettings($quest, $meeting);

        return $quest;
    }

    /**
     * @param $meetingId
     * @return HM_Meeting_MeetingModel|null
     */
    public function getMeeting($meetingId) {
        $meeting = parent::getMeeting($meetingId);
        return ($meeting instanceof HM_Meeting_MeetingModel) ? $meeting : null;
    }

}