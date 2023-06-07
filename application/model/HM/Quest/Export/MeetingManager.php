<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 21.04.2015
 * Time: 19:35
 */

class HM_Quest_Export_MeetingManager extends HM_Export_Meeting_PdfManager {

    protected $_quests = null;

    protected $_errorMessage = "Не выбрано ни одного занятия с типом тест";
    
    public function __construct($meetingIds)
    {
        parent::__construct($meetingIds);
        $questIds = array();
        foreach ($this->_meetings as $meeting) {
            $questId = $meeting->getModuleId();
            if (!in_array($questId, $questIds)) {
                $questIds[$meeting->meeting_id] = $questId;
            }
        }

        $questService = $this->getService('Quest');
        foreach ($questIds as $meetingId => $questId) {
            /** @var HM_Quest_QuestService $questService */
            $this->_quests[$questId] = $questService->fetchAllDependence(
                array('Settings', 'Cluster', 'QuestionQuest'),
                $questService->quoteInto('quest_id = ?', $questId)
            );

            if (count($this->_quests)) {
                $this->_quests = $this->_quests[$questId]->asArrayOfObjects();
            }

            $this->_quests[$questId]->settings = $this->getService('QuestSettings')->fetchAll(
                $this->getService('QuestSettings')->quoteInto(
                    array('quest_id = ? ', ' AND scope_type = ? ', ' AND scope_id = ? '),
                    array($questId, HM_Quest_QuestModel::SETTINGS_SCOPE_MEETING, $meetingId)
                )
            );
        }

        $this->_templateFiles = array(
            'main' => realpath(APPLICATION_PATH.'/../data/templates/export/meeting/main.html'),
            'quest' => realpath(APPLICATION_PATH.'/../data/templates/export/meeting/quest/quest.html'),
            'variant' => realpath(APPLICATION_PATH.'/../data/templates/export/meeting/quest/variant.html')
        );
    }

    protected function filterMeetings($meetingIds)
    {
        if(!is_array($meetingIds)) {
            $meetingIds = explode(',', $meetingIds);
        }
        /** @var HM_Meeting_MeetingService $meetingService */
        $meetingService = $this->getService('Meeting');
        $tests = $meetingService->fetchAll(
            $meetingService->quoteInto(
                array('meeting_id in (?) ', ' AND (typeID = ?', ' or tool = ?)'),
                array($meetingIds, HM_Event_EventModel::TYPE_TEST, HM_Event_EventModel::TYPE_TEST)
            )
        );
        return $tests;
    }

    protected function getMeetingsHtml($variantsCount, $withAnswer)
    {
        $view = Zend_Registry::get('view');
        $questTemplate = array();
        $variantTemplate = file_get_contents($this->_templateFiles['variant']);

        $meetingsHtml = '';
        if (count($this->_meetings)) {
            foreach ($this->_meetings as $test) {
                $questId = $test->getModuleId();
                if (empty($this->_quests[$questId])) {
                    continue;
                }
                $quest = $this->_quests[$questId];
                if ($withAnswer) {
                    $quest->setValue('mode_selection', HM_Quest_QuestModel::MODE_SELECTION_ALL);
                }
                $attempt = HM_Quest_Attempt_AttemptModel::factory($quest->getValues(), 'HM_Quest_Attempt_AttemptModel');
                $attempt->setQuest($quest);
                $variantResult = '';
                for ($i = 1; $i <= $variantsCount; $i++) {
                    //получаем модель варианта
                    $attempt->setupModel();
                    $model = $attempt->getModel();
                    $questions = '';
                    $questionNumber = 1;
                    foreach ($model['questions'] as $question) {
                        $questions .= '<div class="at-form"><div class="at-form-container">
                    <form><div class="quest-question quest-question-' . $question->type . '">
                        ' . $view->question(
                                $question,
                                null,
                                array(
                                    'number' => $questionNumber++,
                                    'template_path' => 'export/',
                                    'with_answer' => $withAnswer
                                )
                            ) . "</div></form></div></div>";

                    }
                    $variantResult = str_replace('{{QUESTIONS}}', $questions, $variantTemplate);
                    $variantResult = str_replace('{{VARIANT_ID}}', "variant-{$test->SHEID}-{$i}", $variantResult);
                    $variantResult = str_replace('{{VARIANT_TITLE}}', _("{$test->title} - Вариант № {$i}"), $variantResult);
                    $questTemplate[] = $variantResult;
                }
            }
        }

        if (count($questTemplate)) {
            $meetingsHtml = implode('<div class="pagebreak"/>', $questTemplate);
        }
        return $meetingsHtml;
    }
}