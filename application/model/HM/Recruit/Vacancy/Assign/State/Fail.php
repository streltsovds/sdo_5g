<?php

class HM_Recruit_Vacancy_Assign_State_Fail extends HM_State_Abstract
{
    public function isNextStateAvailable() {}
    public function onNextState() { return true; }
    public function getActions() {}
    public function getDescription() {}
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

    public function getTitle()
    {
        return _('Прохождение сессии');
    }
    
}
