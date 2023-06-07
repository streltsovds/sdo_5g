<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 11/30/18
 * Time: 3:31 PM
 */

class HM_View_Sidebar_SubjectUpdates extends HM_View_Sidebar_Abstract
{
    const UPDATE_SHOW_DAYS = 30; // не используется
    const UPDATE_SHOW_NUMBER = 10; // используется

    public function getIcon()
    {
        return 'Updates';
    }

    public function getTitle()
    {
        return _('Обновления курса');
    }

    public function getContent()
    {
        $data = [
            'resources' => []
        ];

        /** @var HM_Subject_SubjectModel $subject */
        $subject = $this->getModel();
        $subject->icon= $subject->getDefaultIcon();
        $subject->image = $subject->getUserIcon();

        if ($subject->resources && count($subject->resources)) {
            $resourceIds = $subject->resources->getList('resource_id');
            $resources = $this->getService('Resource')->fetchAll(['resource_id in (?)' => $resourceIds]);

            $data['resources'] = $resources->asArrayOfArrays();
        }

        if (isset($data['resources'])) {

//            $data['resources'] = array_filter($data['resources'], function($resource){
//                $diff = HM_Date::getPeriodSinceDate($resource['updated'], false);
//                return $diff / 86400 < self::DAYS_SHOW_UPDATE;
//            });

            usort($data['resources'], function($res1, $res2){
                return $res1['updated'] < $res2['updated'] ? 1 : -1;
            });

            $data['resources'] = array_slice($data['resources'], 0, self::UPDATE_SHOW_NUMBER);

            array_walk($data['resources'], function (&$resource) {

                if ($resource['updated']) {
                    $resource['updated'] = HM_Date::getPeriodSinceDate($resource['updated']);
                } else {
                    $resource['updated'] = '';
                }

                $resource['viewUrl'] = ($resource['type'] !== HM_Resource_ResourceModel::TYPE_CARD) ? $this->view->url(['module' => 'subject', 'controller' => 'material', 'action' => 'index', 'resource_id' => $resource['resource_id']]) : false;
                $resource['quickViewUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'material', 'action' => 'quick-view', 'resource_id' => $resource['resource_id']]);
                $resource['editUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'material', 'action' => 'edit', 'resource_id' => $resource['resource_id']]);
                $resource['deleteUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'extra', 'action' => 'delete', 'resource_id' => $resource['resource_id']]);
            });
        }

        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->getService('Subject');
        $data['recentActions'] = $subjectService->getRecentActions($subject->subid);
        $data['subject'] = $subject;
        $data['createUrl'] = $this->view->url(['module' => 'kbase', 'controller' => 'resource', 'action' => 'create', 'subject_id' => $subject->subid, 'extras' => 1]);
        $data['materialsUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'materials', 'action' => 'index', 'subject_id' => $subject->subid]);

        $data['editUrl'] = $this->view->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subid' => $subject->subid]);
        $data['showEdit'] = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), [HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL]);

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('subject/updates.tpl', ['data' => $data]);
    }
}
