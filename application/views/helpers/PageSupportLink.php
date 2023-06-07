<?php
class HM_View_Helper_PageSupportLink extends HM_View_Helper_Abstract
{

    public function pageSupportLink()
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';

        return  $protocol . '://' . $_SERVER['HTTP_HOST'] . $this->view->url([
                'module'     => 'techsupport',
                'controller' => 'ajax',
                'action'     => 'post-request'
            ]);
    }
}