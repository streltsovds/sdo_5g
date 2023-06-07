<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 26.09.2014
 * Time: 13:17
 */

class HM_Tc_Session_State_Complete extends HM_State_Complete_Abstract {
    public function isNextStateAvailable() {}
    public function onNextState() { return true; }
    public function getActions() {}
    public function getDescription() {}
    public function initMessage() {}
    public function onNextMessage() {}
    public function onErrorMessage() {}
    public function getCompleteMessage() {}

    public function getSuccessMessage()
    {
        return _('');
    }

    public function getTitle()
    {
        return _('Сессия планирования завершена');
    }
} 