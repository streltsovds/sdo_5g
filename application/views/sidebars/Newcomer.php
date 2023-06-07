<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Newcomer extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'user'; // @todo
    }

    public function getContent()
    {
        return $this->view->partial('newcomer.tpl', ['model' => $this->getModel()]);
    }
}