<?php
class Notice_IndexController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->settings->notifications;
    }
    
    /**
     * @param array $settings
     * Массив текстов шаблонов; 
     */
    public function setNotifications($notifications) 
    {
        $this->openMenuMain($this->page->menu);
        
        foreach ($notifications as $notification) {
            $this->rowAction(Codeception_Registry::get('config')->pages->common->grid->actions->edit, $notification->id);
            $this->I->executeJS("$('#message').val('{$notification->text}')");
            $this->I->click(Codeception_Registry::get('config')->pages->common->form->button->submit);
            $this->I->see($this->pageCommon->grid->messages->success);
        }

        $keys = implode("','", array_keys($notifications));
        $this->rollback("UPDATE notice SET message='' WHERE type IN ('{$keys}')");
    }
}