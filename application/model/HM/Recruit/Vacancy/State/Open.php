<?php
class HM_Recruit_Vacancy_State_Open extends HM_State_Abstract
{
    public function isNextStateAvailable()
    {
        return true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getForms()
    {
        return $this->getDescriptionForm();
    }

    public function getActions()
    {
        $actions = array();
        $params = $this->getParams();
        
        $actions[] = new HM_State_Action_Link(array(
                'url' => array('module' => 'vacancy', 'controller' => 'report', 'action' => 'card', 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'),
                'title' => _('Просмотреть карточку вакансии')
            ), 
            array(), // всем
            $this
        );
        
        if ($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING) {

            $actions[] = new HM_State_Action_Text(array(
                    'title' => _('Переход на следующий этап происходит автоматически при включении в сессию подбора первого кандидата')
                ), 
                array(), 
                $this, 
                HM_State_Action::DECORATE_NEXT
            );

            $actions[] = new HM_State_Action_Link(array(
                    'url' => array('module' => 'vacancy', 'controller' => 'index', 'action' => 'change-state', 'state' => HM_At_Session_SessionModel::STATE_CLOSED, 'vacancy_id' => $params['vacancy_id'], 'baseUrl' => 'recruit'), 
                    'title' => _('Отменить сессию подбора')
                ), array(
                    'roles' => array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)
                ), 
                $this,
                HM_State_Action::DECORATE_FAIL
            );
        }
        
        return $actions;
    }

    public function getDescription()
    {
        return _('На этом этапе уточняются параметры подбора, редактируются методики, участвующие в программе подбора; вакансия размещается на публичных ресурсах.');
    }

    public function initMessage() { return 'initMessage'; }

    public function onNextMessage() { return 'onNextMessage'; }

    public function onErrorMessage() 
    {
        return _("При создании вакансии возникли непредвиденные ошибки.");
    }

    public function getFailMessage()
    {
        return _('Сессия подбора отменена');
    }

    public function getSuccessMessage()
    {
        return _('Этап успешно завершён');
    }

    public function getCurrentStateMessage()
    {
        return _('В процессе');
    }
}