<?php

class HM_At_Session_State_Complete extends HM_State_Complete_Abstract
{
    public function isNextStateAvailable() {}
    
    public function onNextState() {}
    
    public function getActions() 
    {
        
    }
    
    public function getDescription() 
    {
        return _('На этом этапе доступ к оценочным мероприятиям закрыт для пользователей; в системе формируются отчеты по итогам оценочной сессии.');
    }
    
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

}
