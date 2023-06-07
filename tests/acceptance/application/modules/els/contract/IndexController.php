<?php
class Contract_IndexController extends Codeception_Controller_Action
{
    protected $page;
    
    public function init()
    {
        $this->page = Codeception_Registry::get('config')->pages->settings->registration;
    }
    
    /**
     * @param array $settings
     * Массив настроек; 
     * чекбоксы иметь значение boolean
     */
    public function setSettings($settings) 
    {
        $this->openMenuMain($this->page->menu);
//         $this->I->submitForm('.els-content form', $settings);
        foreach ($settings as $name => $value) {
            if (is_bool($value)) {
                $method = $value ? 'checkOption' : 'uncheckOption';
                $this->I->$method("#{$name}");
            } else {
                $this->I->executeJS("$('#{$name}').val('{$value}')");
            }
        }
        $this->I->click(Codeception_Registry::get('config')->pages->common->form->button->submit);
        
        $this->I->see($this->pageCommon->grid->messages->success);
        
        foreach ($settings as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $this->I->seeCheckboxIsChecked("#{$name}");
                }
            }
        }
        
        $keys = implode("','", array_keys($settings));
        $this->rollback("UPDATE OPTIONS SET value=0 WHERE name IN ('{$keys}')");        
    }
}