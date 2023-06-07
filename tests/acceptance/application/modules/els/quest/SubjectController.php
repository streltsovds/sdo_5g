<?php
class Quest_SubjectController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
    }
    
    /**
     * Создание теста в курсе
     * 
     * @param stdObj $test
     * @param stdObj $subject
     * @return int ID созданного курса
     */
    public function createTest($test, $subject, $openExtendedPage = true)
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->tests;
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        $data = [
            'steps' => [
                [
                    'name' => $test->name,
                ],
                [
                    'limit_attempts' => $test->limit_attempts,
                    'limit_time' => $test->limit_time,
                ],
        ]];

        $this->createEntity($data, false);
        $testId = $this->grabEntityId('name', $test->name);

        if (isset($test->questions)) {

            $this->page = Codeception_Registry::get('config')->pages->test->questions;
            $this->I->click('td.grid-count_questions a');
            $this->I->waitForText($test->name, Codeception_Registry::get('config')->global->timeToWait);

            foreach ($test->questions as $question) {

                $data = [
                    'type' => [
                        'type' => 'select',
                        'value' => $question->type
                    ],
                    'question' => [
                        'type' => 'wysiwyg',
                        'value' => $question->text
                    ],
                ];
                $this->createEntity($data, false, false);

                foreach ($question->answers as $answer) {
                    
                    if ($answer->is_correct) {
                        $this->I->checkOption('(//*[@id="is_correct"])[last()]');
                    }
                    $this->I->fillField('(//*[@id="variants__variant__new"])[last()]', $answer->text);

                    // $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
                }

                $this->I->submitForm('.els-content form', array());
                $this->I->see($this->pageCommon->grid->messages->success);
            }
        }

        $this->rollback('delete from questionnaires where quest_id = %d', $testId);
        $this->rollback('delete from quest_question_quests where quest_id = %d', $testId);

        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->tests;

        return $testId;
    }
    
    /**
     * Создание опроса в курсе
     * 
     * @param stdObj $test
     * @param stdObj $subject
     * @return int ID созданного курса
     */
    public function createPoll($poll, $subject, $openExtendedPage = true)
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->polls;
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        $data = [
            'steps' => [
                1 => [
                    'name' => $poll->name,
                ],
                2 => [
                    'poll_mode' => [
                        'type' => 'radio',
                        'value' => $poll->poll_mode,
                        'fieldset' => 'group_test',
                    ],
                    'scale_id' => [
                        'type' => 'select',
                        'value' => $poll->scale->id,
                        'fieldset' => 'group_test',
                    ],
                    'mode_display' => [
                        'type' => 'radio',
                        'value' => $poll->mode_display,
                    ],
                ],
        ]];

        $this->createEntity($data, false);
        $pollId = $this->grabEntityId('name', $poll->name);
        
        if (isset($test->questions)) {
        
            $this->page = Codeception_Registry::get('config')->pages->poll->questions;
            $this->I->click('td.grid-count_questions a');
            $this->I->waitForText($poll->name, Codeception_Registry::get('config')->global->timeToWait);
        
            foreach ($poll->questions as $question) {
        
                $data = [
                    'type' => [
                        'type' => 'select',
                        'value' => $question->type
                    ],
                    'question' => [
                        'type' => 'wysiwyg',
                        'value' => $question->text
                    ],
                ];
                $this->createEntity($data, false);
            }
        }
        

        $this->rollback('delete from questionnaires where quest_id = %d', $pollId);
        $this->rollback('delete from quest_question_quests where quest_id = %d', $pollId);

        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->polls;

        return $pollId;
    }
}