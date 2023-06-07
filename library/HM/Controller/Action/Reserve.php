<?php
class HM_Controller_Action_Reserve extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_reserveId = 0;
    protected $_reserve = null;
    protected $_user = null;

    public function init()
    {
        $this->_reserveId = $this->_getParam('reserve_id', 0);
        $this->_reserve   = $this->getOne(
            $this->getService('HrReserve')->findDependence(['Evaluation', 'User', 'Cycle'], $this->_reserveId)
        );

        if ($this->_reserve) {

            if (count($this->_reserve->user)) {
                $this->_user = $this->_reserve->user->current();
            }

            $this->getService('Process')->initProcess($this->_reserve);
            $this->initContext($this->_reserve);

            $this->view->addSidebar('reserve', [
                'model' => $this->_reserve,

               
            ]);
        }
        
        parent::init();
    }
}