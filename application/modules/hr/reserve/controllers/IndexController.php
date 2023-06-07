<?php
class Reserve_IndexController extends HM_Controller_Action_Reserve
{
    public function programmAction()
    {
        if (count($collection = $this->getService('Programm')->getProgramms(HM_Programm_ProgrammModel::ITEM_TYPE_RESERVE, $this->_reserve->reserve_id, HM_Programm_ProgrammModel::TYPE_RESERVE))) {
            $programm = $collection->current();
            $url = $this->view->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'index', 'baseUrl' => '', 'programm_id' => $programm->programm_id, 'reserve_id' => $this->_reserve->reserve_id));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Программа не найдена')
            ));
            $url = $this->view->url(array(
                'module' => 'programm',
                'controller' => 'list',
                'action' => 'index',
                'baseUrl' => '',
                'programm_id' => null,
                'reserve_id' => null
            ));
            $this->_redirector->gotoUrl($url, array('prependBase' => false));
        }        
    }
    
    public function loginAsAction()
    {
        $this->_request->setParam('user_id', $this->_reserve->user_id);
        parent::loginAsAction();
    }
}
