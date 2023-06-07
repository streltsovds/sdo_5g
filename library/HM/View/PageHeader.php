<?php
class HM_View_PageHeader extends Zend_View
{
    public function __toString()
    {
        $this->setScriptPath(APPLICATION_PATH . '/views/extended');
        $this->addHelperPath(Zend_Registry::get('config')->path->helpers->default, 'HM_View_Helper');
        return $this->render('header.tpl');
    }
    
    public function getTitle()
    {
        $title = '';
        if (!empty($this->panelTitle)) {
            $title .= _($this->panelTitle);
        }
        if (!empty($this->panelTitle) && !empty($this->pageTitle)) {
            $title .= ' › ';
        }
        if (!empty($this->pageTitle)) {
            $title .= _($this->pageTitle);
        }
        return $title;
    }
}