<?php

class HM_Hr_Rotation_State_Complete extends HM_State_Complete_Abstract
{
    public function isNextStateAvailable() {}
    
    public function onNextState() {}
    
    public function getActions() 
    {
        
    }
    
    public function getDescription() 
    {
        return _('Сессия ротации завершается');
    }
    
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

}
