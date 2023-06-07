<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Vacancy extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'vacancy'; // @todo
    }

    public function getContent()
    {
        return $this->view->partial('vacancy.tpl', ['model' => $this->getModel()]);
    }
}