<?php
class HM_Extension_Remover_KnowledgeBaseRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'roles' => [
                HM_Role_Abstract_RoleModel::ROLE_DEVELOPER,
                HM_Role_Abstract_RoleModel::ROLE_MANAGER,
            ],
            'classifierTypes' => [
                HM_Classifier_Link_LinkModel::TYPE_RESOURCE
            ],
            'columns' => [
                'location',
                'chain'
            ],
            'domains' => [
                'Materials',
            ],
            'infoblocks' => [
                'resourcesBlock',
            ],
        ]);
    }

    public function callAfterInitExtensions($event)
    {
        $this->getService('Unmanaged')->removeSearch();
        parent::callAfterInitExtensions($event);
    }
}