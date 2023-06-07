<?php
class Lesson_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->lessons;
    }
    
    /**
     * Создание занятия на курсе
     * 
     * @param stdObj $lesson
     * @return int ID созданного занятия
     */
    public function create($lesson, $subject, $openExtendedPage = true) 
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);

        try {
            $data = [
                'steps' => [
                    1 => [
                        'title' => $lesson->title,
                        'event_id' => [
                            'type' => 'select',
                            'value' => $lesson->event_id,
                        ],
                        'vedomost' => isset($lesson->vedomost) ? [
                            'type' => 'select',
                            'value' => $lesson->vedomost,
                        ] : null,
                        'GroupDate' => isset($lesson->GroupDate) ? [
                            'type' => 'radio',
                            'value' => $lesson->GroupDate,
                            'fieldset' => 'DateLessonGroup',
                        ] : null,
                        'Condition' => isset($lesson->Condition) ? [
                            'type' => 'radio',
                            'value' => $lesson->Condition,
                            'fieldset' => 'ConditionLessonGroup',
                        ] : null,
                        'cond_sheid' => isset($lesson->conditionLesson) ? [
                            'type' => 'select',
                            'value' => $lesson->conditionLesson->id,
                            'fieldset' => 'ConditionLessonGroup',
                        ] : null,
                        'cond_mark' => isset($lesson->cond_mark) ? $lesson->cond_mark : null,
                    ],                
                    2 => [
                        'module' => isset($lesson->module) ? [
                            'type' => 'select',
                            'value' => $lesson->module->id,
                        ] : null,
                        'formula' => isset($lesson->formula) ? (
                            is_object($lesson->formula) ? [
                                'type' => 'select',
                                'value' =>  $lesson->formula->id,
                                'fieldset' => 'formulaGroup',
                            ] : [
                                'type' => 'checkbox',
                                'value' =>  $lesson->formula,
                                'fieldset' => 'formulaGroup',
                            ]
                        ) : null,
                        'limit_attempts' => isset($lesson->limit_attempts) ? $lesson->limit_attempts : null,
                        'show_log' => isset($lesson->show_log) ? [
                            'type' => 'checkbox',
                            'value' => $lesson->show_log
                        ] : null,                        
                    ],                
                    3 => [
                        'teacher' => isset($lesson->teacher) ? [
                            'type' => 'select',
                            'value' => $lesson->teacher->id,
                        ] : null,
                    ],                
                ]
            ];

            
            $this->createEntity($data, false);
            
        } catch (Exception $e) {
            cd($e->getMessage());
        }
                
        $lessonId = $this->grabEntityId('title', $lesson->title);
        
        $this->rollback('delete from scheduleID where SHEID = %d', $lessonId);
        $this->rollback('delete from schedule where SHEID = %d', $lessonId);
        
        return $lessonId;
    }
}