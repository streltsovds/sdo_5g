<?php
class HM_Extension_Remover_OrgstructureRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide(
            ['roles' => [
                HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, // сейчас область ответственности супервайзера строится по оргструктуре
            ],
            'classifierTypes' => [
                HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
            ],
            'columns' => [
                'departments',
                'positions',
                'position',
                'orgStruct',
                'respondent_type',
            ],
            'elements' => [
                'position_id',
                'position_name',
                'respondent_type',
            ],
                'menu' => [
                    'module' => [
                        'orgstructure',
                    ],
                ],
        ]);
    }
}