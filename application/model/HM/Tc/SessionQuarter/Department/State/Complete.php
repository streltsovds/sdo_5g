<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 01.10.2014
 * Time: 13:38
 */

class HM_Tc_SessionQuarter_Department_State_Complete extends HM_State_Complete_Abstract
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
        return _('Согласование консолидированной заявки завершено');
    }

    public function getCurrentStateMessage() {
        return _('Согласование консолидированной заявки завершено');
    }

    public function isPrevStateAvailable() {return true;}
} 