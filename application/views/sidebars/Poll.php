<?php

class HM_View_Sidebar_Poll extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'Material'; // @todo
    }

    public function getTitle()
    {
        return 'Материалы';
    }

    function getContent()
    {
        $data = [];
        $quest = $this->getModel();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isModerator = $aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
            HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        ]);

        if ($isModerator) {
            if ($aclService->isAllowed($this->getService('User')->getCurrentUserRole(), sprintf('mca:%s:%s:%s', 'quest', 'list', 'edit'))) {

                $data['editUrl'] = $this->view->url([
                    'module' => 'quest',
                    'controller' => 'list',
                    'action' => 'edit',
                    'quest_id' => $quest->quest_id
                ], null, true);
            }

            $data['previewUrl'] = $this->view->url([
                'module' => 'quest',
                'controller' => 'lesson',
                'action' => 'info',
                'quest_id' => $quest->quest_id
            ], null, true);
        }

        $data['questTags'] = $this->getService('Tag')->getTags($quest->quest_id, HM_Tag_Ref_RefModel::TYPE_POLL);
        $data['questClassifiers'] = $this->getService('Classifier')->getItemClassifiers($quest->quest_id, HM_Classifier_Link_LinkModel::TYPE_POLL)->asArrayOfArrays();
        $data['quest'] = $quest;

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('poll.tpl', ['data' => $data]);
    }
}