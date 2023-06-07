<?php
/*
 * Тест
 */
class HM_Lesson_Test_TestService extends HM_Lesson_LessonService
{

    /**
     * Устанавливает настройки теста в занятии,
     * ищет настройки в порядке возрастания области видимости:
     * занятие -> курс -> база_знаний
     *
     * @param HM_Quest_QuestModel $quest
     * @param HM_Lesson_LessonModel $lesson
     */
    private function _setQuestLessonSettings($quest, $lesson) {
        if (!$quest || !$lesson) {
            return;
        }

        $questSettings = $quest->getScopeSettings(
            HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON,
            $lesson->SHEID
        );

        if (!$questSettings) {
            $questSettings = $quest->getScopeSettings(
                HM_Quest_QuestModel::SETTINGS_SCOPE_SUBJECT,
                $lesson->CID
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
     * @param HM_Lesson_LessonModel|int $lesson
     * @return HM_Quest_QuestModel|null
     */
    public function getQuest($lesson) {
        if (!($lesson instanceof HM_Lesson_LessonModel)) {
            $lesson = $this->getLesson($lesson);
            if (!$lesson) {
                return null;
            }
        }

        $questId = $lesson->getModuleId();

        if (empty($questId)) {
            return null;
        }

        /** @var HM_Quest_QuestService $questService */
        $questService = $this->getService('Quest');
        $quest = $questService->getQuestWithSettings($questId);
        $this->_setQuestLessonSettings($quest, $lesson);

        return $quest;
    }

    /**
     * @param $lessonId
     * @return HM_Lesson_LessonModel|null
     */
    public function getLesson($lessonId) {
        $lesson = parent::getLesson($lessonId);
        return ($lesson instanceof HM_Lesson_LessonModel) ? $lesson : null;
    }

}