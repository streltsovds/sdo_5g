<?php

class HM_View_Helper_HM extends HM_View_Helper_Abstract
{
    public function HM()
    {
        return HM_Frontend_HM::get();
    }
}