<?php
class Codeception_Controller_Action
{
    const EXTENDED_CONTEXT_SUBJECT = 'subject';
    const EXTENDED_CONTEXT_USER = 'user';

    protected $I;
    protected $scenario;
    
    // это всё блоки из pages.ini
    protected $page; // здесь настройки страницы, с которой сейчас работаем
    protected $pageCommon; // здесь настройки из секции common
    
    public function __construct($scenario) 
    {
        $this->scenario = $scenario;
        $this->I = $scenario->I;
        
        if (method_exists($this, 'init')) {
            $this->init();
        }
        
        $this->pageCommon = Codeception_Registry::get('config')->pages->common;
    }
    
    protected function rollback($sql, $bind = false)
    {
        if ($bind) $sql = sprintf($sql, $bind);
        $this->scenario->addRollback($sql);
    }
    
    
    /****** menus ********/    

    public function openMenuMain($menu)
    {
        if (strpos($menu->group, 'm') !== false) {
            $this->I->click("//li[@data-submenu-id='menu-{$menu->group}']", '.tab-bar');
        } else {
            $this->I->click($menu->group, '.tab-bar');
        }
        
        if (!empty($menu->page)) {
//             $this->I->waitForText($menu->page, Codeception_Registry::get('config')->global->timeToWait, "#{$menu->id}"); // пытается кликать по меню в движении и промахивается
//             $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
            $this->I->waitForElementVisible("#{$menu->id}", Codeception_Registry::get('config')->global->timeToWait); 
            $this->I->click($menu->page, "#{$menu->id}");
        }
        
        $this->I->dontSeeElement('.error-page');
    }

    /**
     * @param unknown $contextType
     * @param unknown $object
     * @param unknown $page
     * @param unknown $openExtendedPage - иногда открывать не надо, мы уже в аккордеоне
     */
    public function openMenuContext($contextType, $object, $page, $openExtendedPage = true)
    {
        if ($openExtendedPage) {
            $this->openExtendedPage($contextType, $object);
        }
        if (isset($page->group)) {
            // $this->I->click($page->group, '.header'); // не работает, т.к. не ссылка
            $this->I->executeJS(sprintf('$(".ui-accordion-header:contains(%s)").click()', "'{$page->group}'"));
            $this->I->waitForText($page->page, Codeception_Registry::get('config')->global->timeToWait);
        }
        $this->I->click($page->page, '#page-context-accordion');
//         $this->I->click($page->page);
        $this->I->waitForText($page->page, Codeception_Registry::get('config')->global->timeToWait);
    }

    // над гридом слева
    public function openMenuCreate($link)
    {
        $this->I->click('.clicker');
        $this->I->waitForText($link, Codeception_Registry::get('config')->global->timeToWait);
        $this->I->click($link, '.dropdown-actions-menu');
    }

    /**
     * Вызывает действие над отдельной строкой из меню справа
     * Работает только если действий несколько и меню как таковое есть
     * Для простых вариантов целесообразно использовать rowAction() 
     * 
     * @param unknown $contextType
     * @param unknown $object
     * @param unknown $page
     */
    public function openMenuGrid($key, $value, $link)
    {
        $this->filterEntity($key, $value);
        $this->I->click('.els-grid tr.els-grid-many-actions td.grid-actions');
        $this->I->waitForText($link, Codeception_Registry::get('config')->global->timeToWait);
        $this->I->click($link, '.grid-row-actions');
    }

    public function openExtendedPage($contextType, $object)
    {
        switch ($contextType) {
            case self::EXTENDED_CONTEXT_SUBJECT:
                return $this->_openExtendedPageSubject($object);
            break;
            default:
            break;
        }
    }
    
    protected function _openExtendedPageSubject($subject)
    {
        if ($this->scenario->getCurrentRole() == HM_Role_Abstract_RoleModel::ROLE_DEAN) {
            
            $page = Codeception_Registry::get('config')->pages->study->subjects->dean;
            
            $this->openMenuMain($page->menu);
            $this->filterEntity('name', $subject->name);
            $this->I->click($subject->name);
        } else {
            
            $page = Codeception_Registry::get('config')->pages->study->subjects;
            
            $this->openMenuMain($page->menu);
            $this->I->click($subject->name);
        }
        $this->I->waitForText($subject->shortname, Codeception_Registry::get('config')->global->timeToWait);
        //$this->I->see($subject->shortname/*, '.bselect'*/);
    }
    
    
    /****** grids ********/
    
    protected function createEntity($steps, $openMenuMain = true, $doAssertion = true)
    {
        if ($openMenuMain) {
            $this->openMenuMain($this->page->menu);
        }
        
        $this->I->click($this->page->links->create);
        
        if (!array_key_exists('steps', $steps)) $steps = array('steps' => array($steps));
        
        while ($data = array_shift($steps['steps'])) {
            
            $complexFields = array_filter($data, function($item) {return is_array($item);});
            $data = array_filter($data, function($item) {return !is_array($item) && ($item !== null);});
            
            if (count($complexFields)) {
                
                $alreadyOpened = array();
                foreach ($complexFields as $key => $complexField) {

                    if (array_key_exists('fieldset', $complexField) && !in_array($complexField['fieldset'], $alreadyOpened)) {
                        $alreadyOpened[] = $fieldset = $complexField['fieldset'];
                        $this->I->click("#fieldset-{$fieldset} legend");
                        $this->I->waitForElementVisible("#fieldset-{$fieldset} dl", Codeception_Registry::get('config')->global->timeToWait);
                    }
                    
                    if (array_key_exists('type', $complexField)) {
                        
                        $type = ucfirst($complexField['type']);
                        $options = isset($complexField['options']) ? $complexField['options'] : array();
                        $method = "fill{$type}Field";
                        if (method_exists($this, $method)) {
                            $this->$method($key, $complexField['value'], $options);
                        }
                    }
                }
            }
            
            $this->I->waitForElement('.els-content form');
            $this->I->submitForm('.els-content form', $data);
        }

        if ($doAssertion) {
            $this->I->see($this->pageCommon->grid->messages->success);
        }
    }
    
    protected function filterEntity($key, $value)
    {
        $this->I->fillField($key, $value);
        $this->I->click($this->pageCommon->grid->filter->apply);
        $this->waitForGrid();
    }
    
    protected function grabEntityId($key, $value)
    {
        $this->filterEntity($key, $value);
        $id = $this->I->grabValueFrom('td.checkboxes input');
        return $id;
    }
    
    protected function rowAction($action, $id)
    {
        if (!empty($id)) {
            $this->I->click($action, "#autogenerated-grid-row-id-{$id}");
        }
    }
    
    protected function massAction($action, $ids = false)
    {
        // почему-то часто в этом месте зависает - не успевает увидеть .gmail-checkbox
        $this->I->wait(5);
        
        if (empty($ids)) {
            $this->I->checkOption('.gmail-checkbox input[type=checkbox]');
            $this->I->selectOption('#_fdiv select', $action);
            $this->I->click($this->pageCommon->grid->mass_actions->submit);
            $this->answerDialog();
            $this->I->see($this->pageCommon->grid->messages->success);
        } else {
            // @todo
        }
    }
    
    protected function answerDialog($yesOrNo = 'yes')
    {
        $this->waitForDialog();
        $this->I->click($this->pageCommon->dialog->$yesOrNo);
    }
    
    protected function waitForDialog()
    {
        $this->I->waitForElement('.ui-dialog', Codeception_Registry::get('config')->global->timeToWait);
    }
    
    protected function waitForGrid()
    {
        $this->I->waitForElementVisible('.gridmod-ajax', Codeception_Registry::get('config')->global->timeToWait);
    }
    
    protected function fillFcbkField($key, $value, $options = array())
    {
        $key = "{$key}_fcbkComplete_input";
        $this->I->fillField($key, $value);

        // @todo: refactor wait
        $this->I->wait(Codeception_Registry::get('config')->global->timeToWait);
//         $this->I->click($value);
//         $this->I->click($value, "#{$key}_fcbkComplete_feed");
        $this->I->pressKey("input[name='{$key}']", WebDriverKeys::ENTER);
    }
    
    protected function fillTextField($key, $value)
    {
        $this->I->fillField("input[name='{$key}']", $value);
    }
    
    protected function fillSelectField($key, $value)
    {
        $this->I->selectOption("select[name='{$key}']", $value);
    }
    
    protected function fillRadioField($key, $value)
    {
        $this->I->selectOption("input[name='{$key}']", $value);
    }
    
    protected function fillCheckboxField($key, $value)
    {
        $method = $value ? 'checkOption' : 'uncheckOption';
        $this->I->$method("input[type='checkbox'][name='{$key}']"); // есть еще и hidden
    }

    protected function fillFileField($key, $value)
    {
        $this->I->attachFile("input[name='{$key}']", $value);
        $this->I->waitForJS("return !$('#submit').attr('disabled')", Codeception_Registry::get('config')->global->timeToWait);
    }

    protected function fillWysiwygField($key, $value)
    {
        $this->I->executeJS("$('#{$key}').val('{$value}')");
    }
}