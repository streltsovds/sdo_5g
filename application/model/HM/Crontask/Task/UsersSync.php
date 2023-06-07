<?php
class HM_Crontask_Task_UsersSync extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface {

	
    protected $_importService = null;

    public function getTaskId() {
        return 'usersSync';
    }

    public function run() 
    {
	$sources = HM_Integration_Abstract_Model::getLdapNames();
	
	foreach($sources as $ldap) {         
		$importManager = new HM_User_Import_AdManager();
        	$userAdService = $this->getServiceLayer('UserAd');   
		$mapper = $userAdService->getMapper();
		$adapter = $mapper->getAdapter();
		$ldapItem = $adapter->getLdap();
		$ldapItem->setLdapOptions($ldap);
		$fetch = $adapter->fetchAllByLdap($ldap);
		$models = $mapper->createModelByLdap($ldap, $fetch);
        	$importManager->init($models); 
        	$importManager->import();                   
	}	
    }
    
    /*
    *
    * @return HM_Service_Abstract
    */
    protected function  getServiceLayer($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}
