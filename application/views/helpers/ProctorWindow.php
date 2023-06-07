<?php
class HM_View_Helper_ProctorWindow extends HM_View_Helper_Abstract
{
    public function proctorWindow()
    {
        return $this->view->render('proctorWindow.tpl');
    }
}