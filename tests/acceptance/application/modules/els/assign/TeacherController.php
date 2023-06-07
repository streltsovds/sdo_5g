<?php
class Assign_TeacherController extends Codeception_Controller_Action
{
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->teachers;
    }
    
    /**
     * Назначает препода на курс 
     * (длинный путь через контекстное меню курса)
     * 
     * @param stdObj $user
     * @param stdObj $subject
     */
    public function assign($user, $subject) 
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page);
        
        $this->I->click($this->page->gridswitcher->all_users);
        $this->waitForGrid();
        $this->filterEntity('fio', $fio = implode(' ', array($user->lastName, $user->firstName))); 

        $this->massAction($this->page->mass_actions->assign);

        $this->I->click($this->page->gridswitcher->teachers);
        $this->waitForGrid();
        $this->filterEntity('fio', $fio);
        
        $this->I->see($fio);
        
        $this->rollback(sprintf('delete from Teachers where MID = %d and cid = %d', $user->id, $subject->id));
    }
}