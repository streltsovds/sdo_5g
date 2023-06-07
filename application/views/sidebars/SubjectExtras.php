<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectExtras extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'AdditionalMaterials';
    }

    public function getTitle()
    {
        return 'Доп. материалы курса';
    }

    public function getContent()
    {
        //init env for uploader
        $this->view->assign(array(
            'folderHash' => Zend_Registry::get('config')->elFinder->root_hash,
        ));
        $subjectModel = $this->_options['model'];

        if(!$this->getService('User')->isEndUser()) {
            $this->getService('Storage')->createSubjectDirs($subjectModel->getValue('subid'));
        }

        $data = [];
        $subject = $this->getModel();
        $subject->icon= $subject->getDefaultIcon();
        $subject->image = $this->view->publicFileToUrlWithHash($subject->getUserIcon());

        if ($subject->resources && count($subject->resources)) {
            $resourceIds = $subject->resources->getList('resource_id');
            $resources = $this->getService('Resource')->fetchAll(['resource_id in (?)' => $resourceIds], 'title');

            $resourcesModels = $resources->asArrayOfObjects();
            $data['resources'] = [];
        }

        if (isset($resourcesModels)) {
            foreach ($resourcesModels as $resourcesModel) {
                $data['resources'][] = $resourcesModel->getDataForExtrasSidebar($subject->subid);
            }
        }
//        $subject->description = '';


//        $data['createUrl'] = $this->view->url(['module' => 'kbase', 'controller' => 'resource', 'action' => 'create', 'subject_id' => $subject->subid, 'extras' => 1]);
        $data['createUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'extra', 'action' => 'create', 'subject_id' => $subject->subid]);
        $data['materialsUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'materials', 'action' => 'index', 'subject_id' => $subject->subid]);

        $data['subject'] = $subject->getPlainData();


        $data['editUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subid' => $subject->subid]);
        $data['showEdit'] = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]);
        $data['currentUser'] = $this->getService('User')->getCurrentUser();

        $currentFolder = $this->getService('Storage')->fetchRow([
            'is_file = ?'=> HM_Storage_StorageService::NOT_FILE,
            'name = ?' => 'extra-materials',
            'subject_id = ?' => $subject->subid,
        ]);
        $folderHash = $currentFolder ? $currentFolder->hash : Zend_Registry::get('config')->elFinder->root_hash;

        $data['elFinder'] = [
            'name' => 'extraMaterials',
            'attribs' => [
                'connectorUrl' => $this->view->url(array(
                    'module' => 'storage',
                    'controller' => 'index',
                    'action' => 'elfinder',
                    'subject' => HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS,
                    'subject_id' => $subject->subid,
                )),
                'isModal' => false,
                'lang' => Zend_Registry::get('config')->wysiwyg->params->language,
                'folderHash' => $folderHash,
            ],
        ];

        $data = HM_Json::encodeErrorSkip($data);

        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        if ($userService->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER) {
            return $this->view->partial('subject/extras-enduser.tpl', [
                'data' => $data,
            ]);
        } else {
            return $this->view->partial('subject/extras-manager.tpl', [
                'data' => $data,
                'finderSubjectName' => HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS
            ]);
        }
    }
}
