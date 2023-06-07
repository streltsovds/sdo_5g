<?php
class HM_Controller_Action_Profile extends HM_Controller_Action implements HM_Controller_Action_Interface_Context
{
    use HM_Controller_Action_Trait_Context;

    protected $_profileId;
    protected $_profile;

    static protected $denyList = [
        // HM_Role_Abstract_RoleModel::ROLE_CURATOR,
        // HM_Role_Abstract_RoleModel::ROLE_TEACHER,
        HM_Role_Abstract_RoleModel::ROLE_DEAN,
        HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        // HM_Role_Abstract_RoleModel::ROLE_ATMANAGER,
        // HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL,
        // HM_Role_Abstract_RoleModel::ROLE_HR,
        // HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        HM_Role_Abstract_RoleModel::ROLE_ADMIN,
        HM_Role_Abstract_RoleModel::ROLE_SIMPLE_ADMIN,
        // HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY,
        // HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL
    ];

    public function init()
    {
        $this->view->profileId = $this->_profileId = $profileId = $this->_getParam('profile_id', 0);
        $this->view->profile = $this->_profile = $this->getOne($this->getService('AtProfile')->findDependence(['CriterionValue'], $profileId));

        if ($this->_profile) {

            $this->initContext($this->_profile);

            $serviceContainer = Zend_Registry::get('serviceContainer');
            $currentRole = $serviceContainer->getService('User')->getCurrentUserRole();

            if ( ! in_array($currentRole, self::$denyList)) {
                $this->view->addSidebar('profile', [
                    'model' => $this->_profile,
                ]);
            }

//            if(Zend_Controller_Front::getInstance()->getRequest()->getActionName()=='index') {
                $this->view->setBackUrl($this->view->url([
                    'baseUrl' => 'at',
                    'module' => 'profile',
                    'controller' => 'list',
                    'action' => 'index',
                ], null, true));
/*            } else {
                $this->view->setBackUrl($this->view->url([
                    'baseUrl' => 'at',
                    'module' => 'profile',
                    'controller' => 'report',
                    'action' => 'index',
                    'profile_id' => $profileId,
                ], null, true));
            }
*/

        }

        parent::init();
    }
}