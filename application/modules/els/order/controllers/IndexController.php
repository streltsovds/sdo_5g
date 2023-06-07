<?php
class Order_IndexController extends HM_Controller_Action
{
    public function skipEventAction()
    {
        if (($programmEventId = $this->_getParam('programm_event_id')) && ($claimantId = $this->_getParam('claimant_id'))) {
            
            if (count($collection = $this->getService('Claimant')->findDependence('User', $claimantId))) {
                $claimant = $collection->current();
                
                $processAbstract = $claimant->getProcess()->getProcessAbstract();
                if ($processAbstract->isStrict()) {
                    $this->getService('Process')->goToNextState($claimant);                
                } else {
                    $stateClass = HM_Process_Type_Programm_AssessmentModel::getStatePrefix() . $programmEventId;
                    $this->getService('Process')->setStateStatus($claimant, $stateClass, HM_State_Abstract::STATE_STATUS_PASSED);
                }     

                if ($claimant->MID) {
                    $data['status'] = HM_Programm_Event_User_UserModel::STATUS_PASSED;
                    $this->getService('ProgrammEventUser')->updateWhere($data, array(
                        'programm_event_id = ?' => $programmEventId,
                        'user_id = ?' => $claimant->MID,
                    ));                    
                }

                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Этап согласования пройден')));
            }            
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось пропустить данный этап')));            
        }
        
        //$this->_redirector->gotoSimple('index', 'list', 'order');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    public function failAction()
    {
        if ($claimantId = $this->_getParam('claimant_id')) {
            
            if (count($collection = $this->getService('Claimant')->findDependence('User', $claimantId))) {
                $claimant = $collection->current();
                $this->getService('Process')->goToFail($claimant);
            }
    
            $this->_flashMessenger->addMessage(_('Заявка отклонена'));
    
        } else {
            $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не отмечены участники')));
        }
        if ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER,
                    HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR
                )
            )) {
            $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        else {
            $this->_redirector->gotoSimple('index', 'list', 'order');
        }
    }    
}
