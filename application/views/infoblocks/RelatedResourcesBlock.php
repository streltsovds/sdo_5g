<?php


class HM_View_Infoblock_RelatedResourcesBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'relatedResourcesBlock';

    public function relatedResourcesBlock($param = null)
    {
        $resource = $options['subject'];
        $services = Zend_Registry::get('serviceContainer');

        $relatedResources = array();
        if (!empty($resource->related_resources)) {
            $where = array('resource_id IN (?)' => explode(',', $resource->related_resources));
            if (!$services->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_MANAGER, HM_Role_Abstract_RoleModel::ROLE_DEVELOPER))) {
                 $where['status = ?'] = HM_Resource_ResourceModel::STATUS_PUBLISHED;               
            }
            $relatedResources = $services->getService('Resource')->fetchAll($where, 'title');
        }
        
        $this->view->resource = $resource;
        $this->view->relatedResources = $relatedResources;
        
        if ((
                $resource->created_by == $services->getService('User')->getCurrentUserId() &&
                $services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEVELOPER)
            ) ||
            $services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_MANAGER) // манагеру всё можно 
        ) {
            $this->view->editable = true;
        }        

		$content = $this->view->render('relatedResourcesBlock.tpl');

        
        return $this->render($content);
    }
}