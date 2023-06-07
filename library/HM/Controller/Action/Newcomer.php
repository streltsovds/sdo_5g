<?php
class HM_Controller_Action_Newcomer extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;
    
    protected $_newcomerId = 0;
    protected $_newcomer = null;
    protected $_user = null;

    public function init()
    {
        $this->_newcomerId = $this->_getParam('newcomer_id', 0);
        $this->_newcomer  = $this->getOne(
            $this->getService('RecruitNewcomer')->findDependence(['Evaluation', 'User', 'Cycle'], $this->_newcomerId)
        );

        if ($this->_newcomer) {

            if (count($this->_newcomer->user)) {
                $this->_user = $this->_newcomer->user->current();
            }

            $this->getService('Process')->initProcess($this->_newcomer);
            $this->initContext($this->_newcomer);

            $this->view->addSidebar('newcomer', [
                'subject' => $this->_newcomer,

               
            ]);
        }

        parent::init();
    }
}