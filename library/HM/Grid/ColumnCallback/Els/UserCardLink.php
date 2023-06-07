<?php

class HM_Grid_ColumnCallback_Els_UserCardLink extends HM_Grid_ColumnCallback_AbstractCardLink
{
    protected static $_aclLoaded = false;

    protected function _checkRights(HM_Acl $acl)
    {
        if (!static::$_aclLoaded) {

            $acl->addModuleResources('user', 'els');

            static::$_aclLoaded = true;
        }

        $cardResource = HM_ControllerAcl::getResourceName('user', 'list', 'view');

        $this->_viewLinkAllowed = $acl->isCurrentAllowed(HM_Acl::RESOURCE_USER_CONTROL_PANEL, HM_Acl::PRIVILEGE_VIEW);
        $this->_cardLinkAllowed = $acl->isCurrentAllowed($cardResource);
    }

    protected function _getViewUrl($id)
    {
        return $this->_url(array(
            'baseUrl'    => '',
            'module'     => 'user',
            'controller' => 'edit',
            'action'     => 'card',
            'user_id'    => $id
        ));
    }

    protected function _getCardUrl($id)
    {
        return $this->_url(array(
            'baseUrl'    => '',
            'module'     => 'user',
            'controller' => 'list',
            'action'     => 'view',
            'user_id'    => $id
        ));
    }

}