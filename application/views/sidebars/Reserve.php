<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Reserve extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'reserve'; // @todo
    }

    public function getContent()
    {
        return $this->view->partial('reserve.tpl', ['model' => $this->getModel()]);
    }
}