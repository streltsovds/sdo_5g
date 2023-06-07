<?php
trait HM_Controller_Action_Trait_Report
{
    public function initReport()
    {
        $this->view->lists = array();
        $this->view->texts = array();
        $this->view->tables = array();
        $this->view->charts = array();
        $this->view->footnotes = array();
        $this->view->status = false;
        
        if ($this->_getParam('print')) {
            $this->view->print = 1;
            $this->_helper->layout->setLayout('blank');
        }

        if ($this->_getParam('pdf')) {
            $this->view->pdf = 1;
            $this->_helper->layout->setLayout('blank');
        }
    }
}