<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Profile extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'briefcase'; // @todo
    }

    public function getTitle()
    {
        return 'Профиль должности';
    }

    public function getContent()
    {
        $profile = $this->getModel();

        $editUrl = $this->view->url([
            'module' => 'profile',
            'controller' => 'list',
            'action' => 'edit',
            'baseUrl' => 'at',
            'gridmod' => '',
            'profile_id' => $profile->profile_id
        ], null, true);

        $data = [
            'profile' => $profile->getData(),
            'icon' => $profile->getUserIcon(),
            'default_icon' => $profile->getDefaultIcon()
        ];

        return $this->view->partial('profile.tpl', ['data' => HM_Json::encodeErrorSkip($data), 'editUrl' => $editUrl]);
    }
}