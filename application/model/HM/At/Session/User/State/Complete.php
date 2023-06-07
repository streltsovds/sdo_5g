<?php

class HM_At_Session_User_State_Complete extends HM_State_Complete_Abstract
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
        return _('Сессия пройдена');
    }

}
