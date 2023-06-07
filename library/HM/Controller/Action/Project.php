<?php
class HM_Controller_Action_Project extends HM_Controller_Action_Extended
{

    protected $service = 'Project';
    protected $idParamName  = 'project_id';
    protected $idFieldName = 'projid';

    public function init()
    {
        if ($this->_getParam('action') === 'edit-services' && $this->_getParam('subject') === 'project') {
            $this->_setParam('project_id', $this->_getParam('subject_id'));
        }
        
        parent::init();

        if (!$this->isAjaxRequest()) {
            
            if ($this->_subject && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
                
                if ($this->_subject->period == HM_Project_ProjectModel::PERIOD_DATES // не совсем верно, есть еще вариант с ограниченной длительностью и он никак не обрабатывается; рассчитываем на то, что скоро появится перевод в прош.обучение по крону и эта проверка не понадобится
                    && strtotime($this->_subject->end) < time()
                    && $this->_subject->period_restriction_type == HM_Project_ProjectModel::PERIOD_RESTRICTION_STRICT
                ){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_NOTICE, 'message' => _('Время обучения на курсе закончилось')));
                    $this->_redirector->gotoSimple('index', 'list', 'project');
                }
            }
        }
    }
}