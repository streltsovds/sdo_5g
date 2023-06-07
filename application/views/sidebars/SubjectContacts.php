<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectContacts extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'services'; // @todo
    }

    public function getTitle()
    {
        return _('Контакты');
    }

    public function getContent()
    {
        $data = ['model' => $this->getModel()];
        return $this->view->partial('subject/contacts.tpl', $data);
    }
}
