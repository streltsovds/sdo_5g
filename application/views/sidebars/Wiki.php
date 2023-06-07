<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 6/20/19
 * Time: 5:08 PM
 */

class HM_View_Sidebar_Wiki extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'wiki'; // @todo
    }

    function getContent()
    {
        return $this->view->render('wiki.tpl', ['model' => $this->getModel()]);
    }
}