<?php
class Assign_AtmanagerController extends Codeception_Controller_Action
{
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->administration->atmanagers;
    }
    
    /**
     * Назначает пользователю роль менеджер по оценке
     * 
     * @param stdObj $user
     */
    public function assign($user) 
    {
        $this->openMenuMain($this->page->menu);
        
        $this->I->click($this->page->gridswitcher->all_users);
        $this->waitForGrid();
        $this->filterEntity('fio', $fio = implode(' ', array($user->lastName, $user->firstName))); 

        $this->massAction($this->page->mass_actions->assign);

        $this->I->click($this->page->gridswitcher->atmanagers);
        $this->waitForGrid();
        $this->filterEntity('fio', $fio);
        
        $this->I->see($fio);
        
        $this->rollback(sprintf('delete from at_managers where user_id = %d', $user->id));
    }
    
    /**
     * Назначает пользователю роль специалист по оценке
     * и ограничивает область ответственности в пределах department
     * 
     * @param stdObj $user
     * @param stdObj $department
     */
    public function assignResponsibility($user, $department) 
    {
        $this->openMenuMain($this->page->menu);
        
        $fio = implode(' ', array($user->lastName, $user->firstName));
        $this->openMenuGrid('fio', $fio, $this->page->actions->responsibility);

        $this->I->selectOption('form input[name=useResponsibility]', $this->page->forms->set);
        
        // @todo: сейчас только только top-level
        $this->I->waitForText($department->name, Codeception_Registry::get('config')->global->timeToWait);
        $this->I->click($department->name);
        
        $this->I->click(Codeception_Registry::get('config')->pages->common->form->button->submit);
        
        $this->I->see($this->page->messages->responsibility->success);
        
        // double check
        $this->openMenuMain($this->page->menu);
        $this->filterEntity('fio', $fio);
        $this->I->see($this->page->messages->specialist);
        
        $this->rollback(sprintf('delete from responsibilities where user_id = %d', $user->id));
    }
}