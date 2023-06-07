<?php
class HM_Extension_Remover_LaborSafetyRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'roles' => [
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
                HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            ],
            'menu' => [
                'id' => [
                    'mca:assign:labor-safety:index',
                ],
            ],
            'noticeClusters' => [
                HM_Notice_NoticeModel::CLUSTER_LABOR_SAFETY,
            ],
        ]);
    }
}