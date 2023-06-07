<?php

class HM_Hr_Reserve_State_Complete extends HM_State_Complete_Abstract
{
    public function isNextStateAvailable() {}
    
    public function getDescription()
    {
        return _('Сессия КР завершается');
    }
}
