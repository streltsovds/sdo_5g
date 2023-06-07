<?php
class Orgstructure_ImportController extends Codeception_Controller_Action
{
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->orgstructure->import;
    }
    
    /**
     * Импорт оргструктуры из _data/$filename
     * 
     * @return $departmentId - id первого попавшегося подразделения верхнего уровня
     */
    public function import($filename) 
    {
        $this->openMenuMain($this->page->menu);

        $this->openMenuCreate($this->page->links->import);
        
        // @todo: рефакторить wait 
        $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
        $this->I->attachFile('.ui-button input', $filename);
        $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
        $this->I->click(Codeception_Registry::get('config')->pages->common->form->button->submit);
        
        $this->I->click($this->page->buttons->next);
        $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
        $this->I->see($this->page->messages->success);
        
        $departments = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAllDependence(array('Parent', 'Descendant'));
        
        $this->rollback('delete from People where MID in (select mid from structure_of_organ)');
        $this->rollback('delete from structure_of_organ');
        
        return $departments;
    }

}