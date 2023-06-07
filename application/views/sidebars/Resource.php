<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Resource extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'Material';
    }
    
    public function getTitle()
    {
        return 'Материалы';
    }

    public function getContent()
    {
        $data = [];

        $services = Zend_Registry::get('serviceContainer');
        $resource = $this->getModel();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isDean = $aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        ]);

        $isTeacher = $aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_TEACHER
        ]);


        if ($isDean || $isTeacher) {
            $editMaterialUrl = [
                'module' => 'kbase',
                'controller' => 'resource',
                'action' => 'edit',
                'resource_id' => $resource->resource_id,
            ];

            if($subjectId = $this->view->subjectId) {
                $editMaterialUrl['subject_id'] = $subjectId;
            }

            $data['editMaterialUrl'] = $this->view->url($editMaterialUrl, null, true);
        }

        // Не через ACL, потому что редактирование инфоресов не имеет контекста курса.
        // А вот ссылка, откуда мы идём на редактирование - имеет
        if($isDean || ($isTeacher && $this->isSubjectContext())) {
            $editUrl = [
                'module' => 'kbase',
                'controller' => 'resource',
                'action' => 'edit-card',
                'resource_id' => $resource->resource_id
            ];
            if($subjectId = $this->view->subjectId) {
                $editUrl['subject_id'] = $subjectId;
            }
            $data['editUrl'] = $this->view->url($editUrl, null, true);
        }

        $materialRating = $this->getService('KbaseAssessment')
            ->getAverage($resource->resource_id, $resource->type);

        $data['materialRating'] = (int) $materialRating['value'];
        $data['materialRatingCount'] = $materialRating['count'];

        $revisions = $services->getService('ResourceRevision')->fetchAll(array('resource_id = ?' => $resource->resource_id), 'revision_id DESC');

        $data['revisions'] = $revisions;

        $relatedResources = array();
        if (!empty($resource->related_resources)) {
            $where = array('resource_id IN (?)' => explode(',', $resource->related_resources));
            if (!$services->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_MANAGER, HM_Role_Abstract_RoleModel::ROLE_DEVELOPER))) {
                $where['status = ?'] = HM_Resource_ResourceModel::STATUS_PUBLISHED;
            }
            $relatedResources = $services->getService('Resource')->fetchAll($where, 'title');

            $relatedResources = $relatedResources->asArrayOfArrays();
        }

        $data['relatedResources'] = $relatedResources;

        $data['resourceTags'] = $this->getService('Tag')->getTags($resource->resource_id, HM_Tag_Ref_RefModel::TYPE_RESOURCE);
        $data['resourceClassifiers'] = $this->getService('Classifier')->getItemClassifiers($resource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)->asArrayOfArrays();

        $substitutions = [
            HM_Resource_ResourceModel::TYPE_FILESET => 'html'
        ];

        foreach ($substitutions as $old => $new) {
            if ($resource->type == $old) $resource->type = $new;
        }

        $data['resource'] = $resource;
        $jsonData = HM_Json::encodeErrorSkip($data);

        $userService = Zend_Registry::get('serviceContainer')->getService('User');

        // пока они совпадают но возможно в будущем у manager'а появится что-то дополнительно
        return $this->view->partial('resource-enduser.tpl', ['data' => $jsonData]);

        if ($userService->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER) {
            return $this->view->partial('resource-enduser.tpl', ['data' => $jsonData]);
        } else {
            return $this->view->partial('resource-manager.tpl', ['data' => $jsonData]);
        }
    }
}
