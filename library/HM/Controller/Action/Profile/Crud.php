<?php
class HM_Controller_Action_Profile_Crud extends HM_Controller_Action_Crud
{
    protected $_profile;

    protected $service     = 'AtProfile';
    protected $idParamName = 'profile_id';
    protected $idFieldName = 'profile_id';
    protected $id          = 0;

    public function init()
    {
        parent::init();

        if ($profileId = $this->_getParam('profile_id', 0)) {
            
            $this->_profile  = $this->getOne(
                $this->getService('AtProfile')->find($profileId)
            );
        }
        
        if (!$this->isAjaxRequest()) {
            
            if ($this->_profile) {
                $this->view->setExtended(
                    array(
                        'subjectName' => 'AtProfile',
                        'subjectId' => $this->_profile->profile_id,
                        'subjectIdParamName' => 'profile_id',
                        'subjectIdFieldName' => 'profile_id',
                        'subject' => $this->_profile
                    )
                );
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не существует сессия оценки')));
                $this->_redirector->gotoSimple('index', 'list', 'profile');
            }
        }        
    }
}