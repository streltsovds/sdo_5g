<?php
class HM_Extension_Remover_ProjectRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'roles' => [
                HM_Role_Abstract_RoleModel::ROLE_CURATOR,
                HM_Role_Abstract_RoleModel::ROLE_MODERATOR,
            ],
            'infoblocks' => [
            ],
            'menu' => [
                'id' => [
                    'mca:assign:curator:index',
                ],
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_PROJECTS,
            ],
        ]);
    }
}