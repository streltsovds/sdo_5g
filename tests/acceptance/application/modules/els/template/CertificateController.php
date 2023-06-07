<?php
class Template_CertificateController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->settings->certificates;
    }
    
    /**
     * @param array $settings
     * Массив настроек; 
     * чекбоксы иметь значение boolean
     */
    public function setTemplate($template) 
    {
        $this->openMenuMain($this->page->menu);
        $this->I->executeJS("$('#template_certificate_text').val('{$template->text}')");
        $this->I->click(Codeception_Registry::get('config')->pages->common->form->button->submit);
        
        $this->I->see($this->pageCommon->grid->messages->success);
        
        $this->rollback("UPDATE OPTIONS SET value='' WHERE name='template_certificate_text'");        
    }
}