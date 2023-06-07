<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 21.04.2015
 * Time: 19:35
 */

class HM_Quest_Export_Manager extends HM_Export_Lesson_PdfManager {

    protected $_quests = null;

    protected $_errorMessage = "Не выбрано ни одного занятия с типом тест";
    
    public function __construct($lessonIds)
    {
        parent::__construct($lessonIds);
        $questIds = array();
        foreach ($this->_lessons as $lesson) {
            $questId = $lesson->getModuleId();
            if (!in_array($questId, $questIds)) {
                $questIds[$lesson->SHEID] = $questId;
            }
        }

        $questService = $this->getService('Quest');
        foreach ($questIds as $lessonId => $questId) {
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
                    array($questId, HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $lessonId)
                )
            );
        }

        $this->_templateFiles = array(
            'main' => realpath(APPLICATION_PATH.'/../data/templates/export/lesson/main.html'),
            'quest' => realpath(APPLICATION_PATH.'/../data/templates/export/lesson/quest/quest.html'),
            'variant' => realpath(APPLICATION_PATH.'/../data/templates/export/lesson/quest/variant.html')
        );
    }

    protected function filterLessons($lessonIds)
    {
        if(!is_array($lessonIds)) {
            $lessonIds = explode(',', $lessonIds);
        }
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        $tests = $lessonService->fetchAll(
            $lessonService->quoteInto(
                array('sheid in (?) ', ' AND (typeID = ?', ' or tool = ?)'),
                array($lessonIds, HM_Event_EventModel::TYPE_TEST, HM_Event_EventModel::TYPE_TEST)
            )
        );
        return $tests;
    }

    protected function getLessonsHtml($variantsCount, $withAnswer)
    {
        $view = Zend_Registry::get('view');
        $questTemplate = array();
        $variantTemplate = file_get_contents($this->_templateFiles['variant']);

        $lessonsHtml = '';
        if (count($this->_lessons)) {
            foreach ($this->_lessons as $test) {
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
            $lessonsHtml = implode('<div class="pagebreak"/>', $questTemplate);
        }
        return $lessonsHtml;
    }
}