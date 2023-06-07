<?php
class Subject_IndexController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->subject->accordion->modules;
    }
    
    /**
     * Создание учебного модуля в курсе 
     * 
     * @param stdObj $module
     * @param stdObj $subject
     * @return int ID созданного модуля
     */
    public function createModule($module, $subject, $openExtendedPage = true) 
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        
        $this->createEntity([
            // @todo
        ]);
        
        $moduleId = $this->grabEntityId('name', $module->name);
        $this->rollback('delete from Courses where CID = %d', $moduleId);
        
        return $moduleId;
    }

    /**
     * Импорт учебного модуля в курс
     *
     * @param stdObj $module
     * @param stdObj $subject
     * @return int ID созданного модуля
     */
    public function importModule($module, $subject, $openExtendedPage = true)
    {
        $this->openMenuContext(Codeception_Controller_Action::EXTENDED_CONTEXT_SUBJECT, $subject, $this->page, $openExtendedPage);
        $this->openMenuCreate($this->page->links->import);

        $this->I->selectOption("select[name='import_type']", $module->type);
        $this->I->attachFile("input[name='zipfile']", $module->file);
        $this->I->submitForm('.els-content form', array());
        $this->I->see($this->pageCommon->grid->messages->success);

        $moduleId = $this->grabEntityId('Title', $module->name);
        $this->rollback('delete from Courses where CID = %d', $moduleId);

        return $moduleId;
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