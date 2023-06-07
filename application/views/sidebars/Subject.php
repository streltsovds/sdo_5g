<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_Subject extends HM_View_Sidebar_Abstract
{
    public function getIcon()
    {
        return 'education'; // @todo
    }

    public function getTitle()
    {
        return 'Обучение';
    }

    public function getContent()
    {
        $userService = Zend_Registry::get('serviceContainer')->getService('User');

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $this->getModel();
        $subject->icon = $subject->getDefaultIcon();
        $subject->image = $subject->getUserIcon();

        $subjectData = $subject->getData();

        foreach ($subjectData as &$subjectProp) {
            $subjectProp = htmlspecialchars(strip_tags($subjectProp));
        }

        $data = [
            'subject' => $subjectData,
            'editUrl' => $this->view->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subid' => $subject->subid]),
            'showEdit' => $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]),
        ];

        $jsonData = HM_Json::encodeErrorSkip($data);


        if ($userService->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_ENDUSER) {
            return $this->view->partial('subject/enduser.tpl', ['data' => $jsonData]);
        } else {
            return $this->view->partial('subject/manager.tpl', ['data' => $jsonData]);
        }
    }
}