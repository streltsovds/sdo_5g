<?php
class Subject_ListController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->study->subjects->dean;
    }
    
    /**
     * Создание учебного курса с настройками по дефолту 
     * 
     * @param stdObj $subject
     * @return int ID созданного курса
     */
    public function create($subject, $settings = array()) 
    {
        $this->createEntity(array(
            'name' => $subject->name,
            'shortname' => $subject->shortname,
            'type' => array(
                'type' => 'radio',
                'value' => $subject->type,
                'fieldset' => 'subjectSubjects2',
            ),
            'reg_type' => array(
                'type' => 'select',
                'value' => $subject->reg_type,
                'fieldset' => 'subjectSubjects2',
            ),
            'claimant_process_id' => array(
                'type' => 'select',
                'value' => $subject->claimant_process_id,
                'fieldset' => 'subjectSubjects2',
            ),
            'period' => array(
                'type' => 'radio',
                'value' => $subject->period,
                'fieldset' => 'subjectPeriodGroup',
            ),
            'longtime' => array(
                'type' => 'text',
                'value' => $subject->longtime,
                'fieldset' => 'subjectPeriodGroup',
            ),
            'scale_id' => array(
                'type' => 'select',                    
                'value' => $subject->scale_id,
                'fieldset' => 'subjectResultsGroup',
            ),
            'auto_mark' => array(
                'type' => 'checkbox',                    
                'value' => $subject->auto_mark,
                'fieldset' => 'subjectResultsGroup',
            ),
            'threshold' => array(
                'type' => 'text',
                'value' => $subject->threshold,
                'fieldset' => 'subjectResultsGroup',
            ),
            'auto_graduate' => array(
                'type' => 'checkbox',
                'value' => $subject->auto_graduate,
                'fieldset' => 'subjectResultsGroup',
            ),
        ));
        
        $subjectId = $this->grabEntityId('name', $subject->name);
        
        $this->rollback('delete from subjects where subid = %d', $subjectId);
        
        return $subjectId;        
    }
    
    /**
     * Вход в курс с соответствующей ролью 
     * (сценарий знает роль текущего actor'а)
     * 
     * @param stdObj $subject
     */
    public function open($subject)
    {
        $this->openExtendedPage(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject);
    }
}