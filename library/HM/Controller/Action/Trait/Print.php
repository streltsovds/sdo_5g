<?php
trait HM_Controller_Action_Trait_Print
{
    public function initPrint()
    {
        $this->view->headScript()->prependFile('/js/lib/underscore-1.3.3.min.js');
        $this->view->hmVue()->init();
        $this->_helper->layout()->setLayout('print');
        return $this;
    }
}